<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audits\CompletePlanterAuditRequest;
use App\Http\Requests\Audits\SavePlanterAuditChecklistRequest;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Services\PlanterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public const BUCKET_ONGOING = 'ongoing';

    public const BUCKET_PASSED = 'passed';

    public const BUCKET_REJECTED = 'rejected';

    public function ongoing(Request $request): View
    {
        return $this->index($request, self::BUCKET_ONGOING);
    }

    public function passed(Request $request): View
    {
        return $this->index($request, self::BUCKET_PASSED);
    }

    public function rejected(Request $request): View
    {
        return $this->index($request, self::BUCKET_REJECTED);
    }

    private function index(Request $request, string $bucket): View
    {
        $user = $request->user('web');
        $round = $this->resolveRound($request, $user);

        $query = PlanterAudit::query()
            ->with(['planter', 'assignee', 'completer'])
            ->where('round', $round)
            ->when($bucket === self::BUCKET_ONGOING, function ($builder) {
                $builder->where('is_current', true)
                    ->whereIn('status', [
                        PlanterAudit::STATUS_QUEUED,
                        PlanterAudit::STATUS_IN_PROGRESS,
                        PlanterAudit::STATUS_IN_REVIEW,
                    ]);
            })
            ->when($bucket === self::BUCKET_PASSED, function ($builder) {
                $builder->whereIn('status', [
                    PlanterAudit::STATUS_PASSED,
                    PlanterAudit::STATUS_CONDITIONAL,
                ]);
            })
            ->when($bucket === self::BUCKET_REJECTED, function ($builder) {
                $builder->where('status', PlanterAudit::STATUS_FAILED);
            })
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $builder->whereHas('planter', function ($planter) use ($search) {
                    $planter->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('nic', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('temporary_id', 'like', $search)
                        ->orWhere('identification_number', 'like', $search);
                });
            })
            ->when($request->filled('district'), function ($builder) use ($request) {
                $builder->whereHas('planter', fn ($planter) => $planter->where('district', $request->string('district')));
            })
            ->when(
                $bucket === self::BUCKET_ONGOING,
                fn ($builder) => $builder->orderByRaw("CASE
                    WHEN status = 'in_review' THEN 1
                    WHEN status = 'in_progress' THEN 2
                    WHEN status = 'queued' THEN 3
                    ELSE 4
                END")->latest('sent_at'),
                fn ($builder) => $builder->latest('completed_at'),
            );

        $audits = $query->paginate(10)->withQueryString();

        $countsBase = PlanterAudit::query()->where('round', $round);

        return view('audits.lobby', [
            'audits' => $audits,
            'bucket' => $bucket,
            'round' => $round,
            'filters' => $request->only(['q', 'district']),
            'stats' => [
                'ongoing' => (clone $countsBase)->where('is_current', true)->whereIn('status', [
                    PlanterAudit::STATUS_QUEUED,
                    PlanterAudit::STATUS_IN_PROGRESS,
                    PlanterAudit::STATUS_IN_REVIEW,
                ])->count(),
                'passed' => (clone $countsBase)->whereIn('status', [
                    PlanterAudit::STATUS_PASSED,
                    PlanterAudit::STATUS_CONDITIONAL,
                ])->count(),
                'rejected' => (clone $countsBase)->where('status', PlanterAudit::STATUS_FAILED)->count(),
            ],
            'canWork' => $user->canWorkAuditRound($round),
            'canDispatch' => $user->canDispatchAudits(),
            'listRoute' => match ($bucket) {
                self::BUCKET_PASSED => 'admin.audits.passed',
                self::BUCKET_REJECTED => 'admin.audits.rejected',
                default => 'admin.audits.ongoing',
            },
        ]);
    }

    public function show(Request $request, PlanterAudit $audit): View
    {
        abort_unless($request->user('web')->canViewAuditRound($audit->round), 403);

        $audit->load([
            'planter',
            'assignee',
            'sender',
            'completer',
            'answers.item',
        ]);

        $attemptHistory = PlanterAudit::query()
            ->with(['completer'])
            ->where('planter_id', $audit->planter_id)
            ->where('round', $audit->round)
            ->orderByDesc('attempt_number')
            ->get();

        $currentAttempt = $attemptHistory->firstWhere('is_current', true);

        $grouped = $audit->answers
            ->sortBy(fn ($answer) => sprintf('%s-%04d', $answer->item?->module, $answer->item?->sort_order ?? 0))
            ->groupBy(fn ($answer) => $answer->item?->module ?? 'other');

        return view('audits.show', [
            'audit' => $audit,
            'attemptHistory' => $attemptHistory,
            'currentAttempt' => $currentAttempt,
            'groupedAnswers' => $grouped,
            'progress' => $audit->checklistProgress(),
            'canWork' => $request->user('web')->canWorkAuditRound($audit->round) && $audit->is_current,
            'canDispatch' => $request->user('web')->canDispatchAudits(),
        ]);
    }

    public function sendFirst(Request $request, Planter $planter, PlanterAuditService $audits): RedirectResponse
    {
        try {
            $audit = $audits->sendToFirstAudit($planter, $request->user('web'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.audits.show', $audit)
            ->with('success', 'Sent to First Audit.');
    }

    public function sendFinal(Request $request, Planter $planter, PlanterAuditService $audits): RedirectResponse
    {
        try {
            $audit = $audits->sendToFinalAudit($planter, $request->user('web'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.audits.show', $audit)
            ->with('success', 'Sent to Final Audit.');
    }

    public function start(Request $request, PlanterAudit $audit, PlanterAuditService $audits): RedirectResponse
    {
        try {
            $audits->start($audit, $request->user('web'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return back()->with('success', $audit->roundLabel().' started.');
    }

    public function saveChecklist(
        SavePlanterAuditChecklistRequest $request,
        PlanterAudit $audit,
        PlanterAuditService $audits,
    ): RedirectResponse {
        try {
            $audits->saveChecklistAnswers(
                $audit,
                $request->user('web'),
                $request->validated('answers', []),
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return back()->with('success', 'Checklist answers saved.');
    }

    public function submit(Request $request, PlanterAudit $audit, PlanterAuditService $audits): RedirectResponse
    {
        try {
            $audits->submitForReview($audit, $request->user('web'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return back()->with('success', 'Checklist submitted for review.');
    }

    public function complete(
        CompletePlanterAuditRequest $request,
        PlanterAudit $audit,
        PlanterAuditService $audits,
    ): RedirectResponse {
        try {
            $audits->completeRound(
                $audit,
                $request->user('web'),
                $request->validated('status'),
                $request->validated('outcome_notes') ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return back()->with('success', $audit->fresh()->roundLabel().' completed.');
    }

    public function reopen(Request $request, PlanterAudit $audit, PlanterAuditService $audits): RedirectResponse
    {
        try {
            $fresh = $audits->reopen($audit, $request->user('web'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['audit' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.audits.show', $fresh)
            ->with('success', $fresh->roundLabel().' attempt '.$fresh->attempt_number.' started. Previous result kept in history.');
    }

    private function resolveRound(Request $request, $user): string
    {
        $requested = (string) $request->string('round');

        if (in_array($requested, [PlanterAudit::ROUND_FIRST, PlanterAudit::ROUND_FINAL], true)
            && $user->canViewAuditRound($requested)) {
            return $requested;
        }

        if ($user->isFinalAuditor() && ! $user->isAdmin()) {
            return PlanterAudit::ROUND_FINAL;
        }

        if ($user->isFirstAuditor() && ! $user->isAdmin()) {
            return PlanterAudit::ROUND_FIRST;
        }

        return PlanterAudit::ROUND_FIRST;
    }
}
