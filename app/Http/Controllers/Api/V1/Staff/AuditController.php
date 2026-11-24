<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Audits\CompletePlanterAuditRequest;
use App\Http\Requests\Audits\SavePlanterAuditChecklistRequest;
use App\Http\Resources\Api\PlanterAuditResource;
use App\Models\AuditChecklistItem;
use App\Models\PlanterAudit;
use App\Models\User;
use App\Services\PlanterAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AuditController extends ApiController
{
    private const BUCKETS = ['ongoing', 'passed', 'rejected'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $round = $request->string('round')->toString() ?: $this->defaultRound($user);
        $bucket = $request->string('bucket')->toString() ?: 'ongoing';

        abort_unless(in_array($round, [PlanterAudit::ROUND_FIRST, PlanterAudit::ROUND_FINAL], true), 422, 'Invalid round.');
        abort_unless(in_array($bucket, self::BUCKETS, true), 422, 'Invalid bucket.');
        abort_unless($user->canViewAuditRound($round), 403);

        $audits = PlanterAudit::query()
            ->with(['planter', 'assignee', 'completer'])
            ->where('round', $round)
            ->when($bucket === 'ongoing', fn ($query) => $query->where('is_current', true)
                ->whereIn('status', [PlanterAudit::STATUS_QUEUED, PlanterAudit::STATUS_IN_PROGRESS, PlanterAudit::STATUS_IN_REVIEW]))
            ->when($bucket === 'passed', fn ($query) => $query
                ->whereIn('status', [PlanterAudit::STATUS_PASSED, PlanterAudit::STATUS_CONDITIONAL]))
            ->when($bucket === 'rejected', fn ($query) => $query->where('status', PlanterAudit::STATUS_FAILED))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->whereHas('planter', function ($planter) use ($search) {
                    $planter->where('name', 'like', $search)
                        ->orWhere('nic', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('temporary_id', 'like', $search)
                        ->orWhere('identification_number', 'like', $search);
                });
            })
            ->when($request->filled('district_id'), fn ($query) => $query->whereHas(
                'planter',
                fn ($planter) => $planter->where('district_id', $request->integer('district_id')),
            ))
            ->when($bucket === 'ongoing', fn ($query) => $query->latest('sent_at'), fn ($query) => $query->latest('completed_at'))
            ->paginate(min($request->integer('per_page', 20), 100))
            ->withQueryString();

        return PlanterAuditResource::collection($audits)->response();
    }

    public function show(Request $request, PlanterAudit $audit): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->canViewAuditRound($audit->round), 403);

        $audit->load(['planter', 'assignee', 'sender', 'completer', 'answers.item']);

        return response()->json($this->detail($user, $audit));
    }

    public function start(Request $request, PlanterAudit $audit, PlanterAuditService $audits): JsonResponse
    {
        abort_unless($request->user()->canWorkAuditRound($audit->round), 403);

        return $this->perform(fn () => $audits->start($audit, $request->user()), $request, $audit);
    }

    public function saveChecklist(Request $request, PlanterAudit $audit, PlanterAuditService $audits): JsonResponse
    {
        $data = $this->validatedFrom(SavePlanterAuditChecklistRequest::class, $request);

        return $this->perform(
            fn () => $audits->saveChecklistAnswers($audit, $request->user(), $data['answers'] ?? []),
            $request,
            $audit,
        );
    }

    public function submit(Request $request, PlanterAudit $audit, PlanterAuditService $audits): JsonResponse
    {
        abort_unless($request->user()->canWorkAuditRound($audit->round), 403);

        return $this->perform(fn () => $audits->submitForReview($audit, $request->user()), $request, $audit);
    }

    public function complete(Request $request, PlanterAudit $audit, PlanterAuditService $audits): JsonResponse
    {
        $data = $this->validatedFrom(CompletePlanterAuditRequest::class, $request);

        return $this->perform(
            fn () => $audits->completeRound(
                $audit,
                $request->user(),
                $data['status'],
                $data['outcome_notes'] ?? null,
                $data['signature'] ?? null,
            ),
            $request,
            $audit,
        );
    }

    public function reopen(Request $request, PlanterAudit $audit, PlanterAuditService $audits): JsonResponse
    {
        try {
            $fresh = $audits->reopen($audit, $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['audit' => new PlanterAuditResource($fresh)], 201);
    }

    /**
     * Runs a service call and returns the refreshed audit detail, or a 422 with the reason.
     */
    private function perform(callable $action, Request $request, PlanterAudit $audit): JsonResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $audit = $audit->fresh(['planter', 'assignee', 'sender', 'completer', 'answers.item']);

        return response()->json($this->detail($request->user(), $audit));
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(User $user, PlanterAudit $audit): array
    {
        $attempts = PlanterAudit::query()
            ->with('completer')
            ->where('planter_id', $audit->planter_id)
            ->where('round', $audit->round)
            ->orderByDesc('attempt_number')
            ->get();

        $checklist = $audit->answers
            ->sortBy(fn ($answer) => sprintf('%s-%04d', $answer->item?->module, $answer->item?->sort_order ?? 0))
            ->groupBy(fn ($answer) => $answer->item?->module ?? 'other')
            ->map(fn ($answers, $module) => [
                'module' => $module,
                'label' => AuditChecklistItem::modules()[$module] ?? strtoupper((string) $module),
                'items' => $answers->values()->map(fn ($answer) => [
                    'answer_id' => $answer->id,
                    'item_id' => $answer->audit_checklist_item_id,
                    'code' => $answer->item?->code,
                    'label' => $answer->item?->label,
                    'description' => $answer->item?->description,
                    'result' => $answer->result,
                    'comment' => $answer->comment,
                ])->values(),
            ])
            ->values();

        return [
            'audit' => new PlanterAuditResource($audit),
            'signature' => $audit->isSigned() ? $audit->signature : null,
            'progress' => $audit->checklistProgress(),
            'checklist' => $checklist,
            'attempts' => $attempts->map(fn (PlanterAudit $attempt) => [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'is_current' => (bool) $attempt->is_current,
                'status' => $attempt->status,
                'status_label' => $attempt->statusLabel(),
                'outcome_notes' => $attempt->outcome_notes,
                'completed_at' => $attempt->completed_at,
                'completed_by' => $attempt->completer?->name,
            ])->values(),
            'permissions' => [
                'can_work' => $user->canWorkAuditRound($audit->round) && $audit->is_current,
                'can_dispatch' => $user->canDispatchAudits(),
            ],
        ];
    }

    private function defaultRound(User $user): string
    {
        return $user->isSecondAuditor() && ! $user->isAdmin()
            ? PlanterAudit::ROUND_FINAL
            : PlanterAudit::ROUND_FIRST;
    }
}
