<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Planters\RejectPlanterRequest;
use App\Http\Requests\Planters\StorePlanterRequest;
use App\Http\Requests\Planters\UpdatePlanterRequest;
use App\Http\Resources\Api\PlanterResource;
use App\Models\ActivityLog;
use App\Models\Planter;
use App\Models\User;
use App\Services\NotifySmsService;
use App\Services\PlanterAuditService;
use App\Services\PlanterIdentityService;
use App\Services\PlanterRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlanterController extends ApiController
{
    /** Registry list (approved by default). Use status=pending for the registration lobby. */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->canViewRegistry(), 403);

        $status = $request->string('status')->toString() ?: Planter::STATUS_APPROVED;
        abort_unless(array_key_exists($status, Planter::statuses()), 422, 'Invalid status.');

        $planters = Planter::query()
            ->where('status', $status)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('nic', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('temporary_id', 'like', $search)
                        ->orWhere('identification_number', 'like', $search);
                });
            })
            ->when($request->filled('district_id'), fn ($query) => $query->where('district_id', $request->integer('district_id')))
            ->when($request->filled('registration_type'), fn ($query) => $query->where('registration_type', $request->string('registration_type')))
            ->when($status === Planter::STATUS_PENDING, fn ($query) => $query->oldest(), fn ($query) => $query->latest())
            ->paginate(min($request->integer('per_page', 20), 100))
            ->withQueryString();

        return PlanterResource::collection($planters)->response();
    }

    public function store(Request $request, PlanterRegistryService $registry): JsonResponse
    {
        abort_unless($request->user()->canManagePlanterRecords(), 403);

        $planter = $registry->createFromStaff(
            $this->validatedFrom(StorePlanterRequest::class, $request),
            $request->user(),
        );

        return response()->json(['planter' => new PlanterResource($planter)], 201);
    }

    public function show(Request $request, Planter $planter, PlanterIdentityService $identity, NotifySmsService $sms): JsonResponse
    {
        abort_unless($request->user()->canViewRegistry(), 403);

        return response()->json($this->detail($request->user(), $planter, $identity, $sms));
    }

    /**
     * Mobile app: staff scan a planter's tracking QR (the reference number) and get the full
     * record, the audits they may work on and the actions they are allowed to take.
     */
    public function scan(Request $request, string $reference, PlanterIdentityService $identity, NotifySmsService $sms): JsonResponse
    {
        abort_unless($request->user()->canViewRegistry(), 403);

        $planter = $identity->findByReference($reference);

        abort_unless($planter, 404, 'No planter found for that QR code.');

        ActivityLog::record('planter.scanned', $planter, ['reference' => $planter->temporary_id]);

        $payload = $this->detail($request->user(), $planter, $identity, $sms);

        $payload['audits'] = collect([$planter->firstAudit, $planter->finalAudit])
            ->filter()
            ->map(fn ($audit) => [
                'id' => $audit->id,
                'round' => $audit->round,
                'status' => $audit->status,
                'is_current' => (bool) $audit->is_current,
                'can_work' => $request->user()->canWorkAuditRound($audit->round),
            ])
            ->values()
            ->all();

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(User $user, Planter $planter, PlanterIdentityService $identity, NotifySmsService $sms): array
    {
        $planter = $identity->ensureIssued($planter)
            ->load(['approver', 'firstAudit', 'finalAudit', 'currentCertificate', 'latestCertificate']);

        return [
            'planter' => new PlanterResource($planter),
            'audit_timeline' => $planter->auditTimelineTree(),
            'qr_payload' => $identity->qrPayload($planter),
            'certificate_qr_payload' => $planter->currentCertificate?->isIssued()
                ? $identity->certificateQrPayload($planter->currentCertificate)
                : null,
            'sms_resend_actions' => $sms->availableResendActions($planter),
            'permissions' => $this->planterPermissions($user, $planter),
        ];
    }

    public function update(Request $request, Planter $planter, PlanterRegistryService $registry): JsonResponse
    {
        abort_unless($request->user()->canManagePlanterRecords(), 403);

        $planter = $registry->updateFromStaff(
            $planter,
            $this->validatedFrom(UpdatePlanterRequest::class, $request),
            $request->user(),
        );

        return response()->json(['planter' => new PlanterResource($planter)]);
    }

    public function destroy(Request $request, Planter $planter, PlanterRegistryService $registry): JsonResponse
    {
        abort_unless($request->user()->canDeletePlanters(), 403);

        $registry->deleteWithDocuments($planter);

        return response()->json(['message' => 'Registry entry deleted.']);
    }

    public function approve(Request $request, Planter $planter, PlanterIdentityService $identity): JsonResponse
    {
        abort_unless($request->user()->canReviewRegistrations(), 403);

        $planter = $identity->approve($planter, $request->user()->id);

        return response()->json(['planter' => new PlanterResource($planter)]);
    }

    public function reject(Request $request, Planter $planter, PlanterIdentityService $identity): JsonResponse
    {
        $data = $this->validatedFrom(RejectPlanterRequest::class, $request);

        $planter = $identity->reject($planter, $request->user()->id, $data['rejection_reason']);

        return response()->json(['planter' => new PlanterResource($planter)]);
    }

    public function applicationDocument(Planter $planter): BinaryFileResponse
    {
        abort_unless(
            $planter->hasApplicationDocument() && Storage::disk('local')->exists($planter->application_document),
            404,
            'No registration form on file.',
        );

        return Storage::disk('local')->download(
            $planter->application_document,
            $this->downloadName($planter, 'registration-form', $planter->application_document),
        );
    }

    public function priorCertificateDocument(Planter $planter): BinaryFileResponse
    {
        abort_unless(
            $planter->hasPriorCertificateDocument() && Storage::disk('local')->exists($planter->prior_certificate_document),
            404,
            'No prior certificate on file.',
        );

        return Storage::disk('local')->download(
            $planter->prior_certificate_document,
            $this->downloadName($planter, 'prior-certificate', $planter->prior_certificate_document),
        );
    }

    public function resendSms(Request $request, Planter $planter, NotifySmsService $sms): JsonResponse
    {
        abort_unless($request->user()->canResendPlanterSms(), 403);

        $data = $request->validate(['template' => ['required', 'string', 'max:80']]);

        if (! $sms->resendStep($planter, $data['template'])) {
            return response()->json([
                'message' => 'Could not resend SMS. Check the planter phone number and Notify.lk settings.',
            ], 422);
        }

        return response()->json(['message' => 'SMS resent to '.$planter->phone.'.']);
    }

    public function dispatchFirstAudit(Request $request, Planter $planter, PlanterAuditService $audits): JsonResponse
    {
        abort_unless($request->user()->canDispatchAudits(), 403);

        try {
            $audit = $audits->sendToFirstAudit($planter, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['audit_id' => $audit->id, 'round' => $audit->round, 'status' => $audit->status], 201);
    }

    public function dispatchSecondAudit(Request $request, Planter $planter, PlanterAuditService $audits): JsonResponse
    {
        abort_unless($request->user()->canDispatchAudits(), 403);

        try {
            $audit = $audits->sendToFinalAudit($planter, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['audit_id' => $audit->id, 'round' => $audit->round, 'status' => $audit->status], 201);
    }

    /**
     * @return array<string, bool>
     */
    private function planterPermissions(User $user, Planter $planter): array
    {
        return [
            'can_edit' => $user->canManagePlanterRecords(),
            'can_delete' => $user->canDeletePlanters(),
            'can_approve' => $user->canReviewRegistrations() && $planter->isPending(),
            'can_reject' => $user->canReviewRegistrations() && $planter->isPending(),
            'can_dispatch_first_audit' => $user->canDispatchAudits() && $planter->canSendToFirstAudit(),
            'can_dispatch_second_audit' => $user->canDispatchAudits() && $planter->canSendToFinalAudit(),
            'can_issue_certificate' => $user->canManageCertificates() && $planter->canIssueCertificate(),
            'can_resend_sms' => $user->canResendPlanterSms(),
        ];
    }

    private function downloadName(Planter $planter, string $label, string $path): string
    {
        $basename = str_replace('/', '-', $planter->identification_number ?: $planter->temporary_id);

        return $basename.'-'.$label.'.'.pathinfo($path, PATHINFO_EXTENSION);
    }
}
