<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Planters\OfflineRegisterPlanterRequest;
use App\Http\Requests\Planters\RegisterPlanterRequest;
use App\Http\Requests\Planters\SetPlanterPasswordRequest;
use App\Models\Planter;
use App\Services\NotifySmsService;
use App\Services\PlanterIdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanterRegistrationController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $data = collect($this->validatedFrom(RegisterPlanterRequest::class, $request))
            ->except('prior_certificate_document')
            ->all();

        if ($request->hasFile('prior_certificate_document')) {
            $data['prior_certificate_document'] = $request->file('prior_certificate_document')
                ->store('planter-certificates', 'local');
        }

        $planter = $this->createPendingPlanter($data, Planter::TYPE_ONLINE);
        app(NotifySmsService::class)->sendRegistrationSubmitted($planter);

        return $this->submitted($planter);
    }

    public function registerOffline(Request $request): JsonResponse
    {
        $data = collect($this->validatedFrom(OfflineRegisterPlanterRequest::class, $request))
            ->except('application_document')
            ->all();

        $data['application_document'] = $request->file('application_document')
            ->store('planter-applications', 'local');

        $planter = $this->createPendingPlanter($data, Planter::TYPE_OFFLINE);
        app(NotifySmsService::class)->sendRegistrationSubmitted($planter);

        return $this->submitted($planter);
    }

    /**
     * Approved planters create their password here, then sign in with the SPPNR ID.
     */
    public function setPassword(Request $request): JsonResponse
    {
        $form = $this->formFrom(SetPlanterPasswordRequest::class, $request);
        $planter = $form->planter();
        $planter->update(['password' => $form->validated()['password']]);

        return response()->json([
            'message' => 'Password created. You can now sign in with your SPPNR ID.',
        ]);
    }

    /**
     * Lets a planter check progress before approval. Needs the reference from the
     * registration response and the NIC, so one number alone is not enough.
     */
    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'temporary_id' => ['required', 'string', 'max:40'],
            'nic' => ['required', 'string', 'max:20'],
        ]);

        $planter = Planter::query()
            ->where('temporary_id', trim($data['temporary_id']))
            ->where('nic', trim($data['nic']))
            ->first();

        if (! $planter) {
            return response()->json(['message' => 'No registration matches these details.'], 404);
        }

        return response()->json([
            'temporary_id' => $planter->temporary_id,
            'status' => $planter->status,
            'status_label' => Planter::statuses()[$planter->status] ?? $planter->status,
            'identification_number' => $planter->isApproved() ? $planter->identification_number : null,
            'rejection_reason' => $planter->isRejected() ? $planter->rejection_reason : null,
            'can_set_password' => $planter->isApproved() && ! $planter->hasPassword(),
        ]);
    }

    private function submitted(Planter $planter): JsonResponse
    {
        return response()->json([
            'message' => 'Registration submitted. Please wait for approval; you will receive an SMS.',
            'temporary_id' => $planter->temporary_id,
            'status' => $planter->status,
        ], 201);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createPendingPlanter(array $data, string $type): Planter
    {
        return DB::transaction(function () use ($data, $type) {
            unset($data['password']);
            $data['status'] = Planter::STATUS_PENDING;
            $data['registration_type'] = $type;
            $data = \App\Support\PlanterLocation::hydrateNames($data);

            $data = app(PlanterIdentityService::class)->assignTemporaryId($data);

            return Planter::query()->create($data);
        });
    }
}
