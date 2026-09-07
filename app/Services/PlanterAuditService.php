<?php

namespace App\Services;

use App\Models\AuditChecklistItem;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\PlanterAuditChecklistAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanterAuditService
{
    public function sendToFirstAudit(Planter $planter, User $actor): PlanterAudit
    {
        abort_unless($planter->isApproved(), 422, 'Only approved registry entries can be sent to audit.');
        abort_unless($actor->canDispatchAudits(), 403);

        if ($planter->audits()->where('round', PlanterAudit::ROUND_FIRST)->exists()) {
            throw new InvalidArgumentException('This entry is already in First Audit.');
        }

        return DB::transaction(function () use ($planter, $actor) {
            $audit = PlanterAudit::query()->create([
                'planter_id' => $planter->id,
                'round' => PlanterAudit::ROUND_FIRST,
                'attempt_number' => 1,
                'is_current' => true,
                'status' => PlanterAudit::STATUS_QUEUED,
                'sent_by' => $actor->id,
                'sent_at' => now(),
            ]);

            $this->seedChecklistAnswers($audit);
            $this->syncPlanterSummary($planter->fresh(['firstAudit', 'finalAudit']));

            return $audit->fresh(['answers']);
        });
    }

    public function sendToFinalAudit(Planter $planter, User $actor): PlanterAudit
    {
        abort_unless($planter->isApproved(), 422, 'Only approved registry entries can be sent to audit.');
        abort_unless($actor->canDispatchAudits(), 403);

        $planter->loadMissing('firstAudit');

        if (! $planter->firstAudit?->canProceedToFinal()) {
            throw new InvalidArgumentException('First Audit must pass or be conditional before Final Audit.');
        }

        if ($planter->audits()->where('round', PlanterAudit::ROUND_FINAL)->exists()) {
            throw new InvalidArgumentException('This entry is already in Final Audit.');
        }

        return DB::transaction(function () use ($planter, $actor) {
            $audit = PlanterAudit::query()->create([
                'planter_id' => $planter->id,
                'round' => PlanterAudit::ROUND_FINAL,
                'attempt_number' => 1,
                'is_current' => true,
                'status' => PlanterAudit::STATUS_QUEUED,
                'sent_by' => $actor->id,
                'sent_at' => now(),
            ]);

            $this->seedChecklistAnswers($audit);
            $this->syncPlanterSummary($planter->fresh(['firstAudit', 'finalAudit']));

            return $audit->fresh(['answers']);
        });
    }

    public function start(PlanterAudit $audit, User $actor): PlanterAudit
    {
        abort_unless($actor->canWorkAuditRound($audit->round), 403);
        abort_unless($audit->is_current, 422, 'Only the current audit attempt can be worked.');

        if (! in_array($audit->status, [PlanterAudit::STATUS_QUEUED, PlanterAudit::STATUS_IN_REVIEW], true)) {
            if ($audit->status === PlanterAudit::STATUS_IN_PROGRESS) {
                return $audit;
            }

            throw new InvalidArgumentException('This audit cannot be started from its current status.');
        }

        $audit->update([
            'status' => PlanterAudit::STATUS_IN_PROGRESS,
            'assigned_to' => $audit->assigned_to ?: $actor->id,
            'started_at' => $audit->started_at ?: now(),
        ]);

        $this->syncPlanterSummary($audit->planter->fresh(['firstAudit', 'finalAudit']));

        return $audit->refresh();
    }

    /**
     * @param  array<int, array{result?: string, comment?: string|null}>  $answers
     */
    public function saveChecklistAnswers(PlanterAudit $audit, User $actor, array $answers): PlanterAudit
    {
        abort_unless($actor->canWorkAuditRound($audit->round), 403);
        abort_unless($audit->is_current, 422, 'Only the current audit attempt can be edited.');
        abort_unless($audit->isOpen(), 422, 'Completed audits cannot be edited.');

        if ($audit->status === PlanterAudit::STATUS_QUEUED) {
            $this->start($audit, $actor);
            $audit->refresh();
        }

        DB::transaction(function () use ($audit, $actor, $answers) {
            foreach ($answers as $itemId => $payload) {
                $answer = $audit->answers()
                    ->where('audit_checklist_item_id', $itemId)
                    ->first();

                if (! $answer) {
                    continue;
                }

                $result = $payload['result'] ?? PlanterAuditChecklistAnswer::RESULT_PENDING;

                if (! array_key_exists($result, PlanterAuditChecklistAnswer::results())) {
                    continue;
                }

                $answer->update([
                    'result' => $result,
                    'comment' => filled($payload['comment'] ?? null) ? trim((string) $payload['comment']) : null,
                    'answered_by' => $actor->id,
                    'answered_at' => now(),
                ]);
            }

            if ($audit->status === PlanterAudit::STATUS_IN_PROGRESS) {
                $audit->update(['status' => PlanterAudit::STATUS_IN_PROGRESS]);
            }
        });

        $this->syncPlanterSummary($audit->planter->fresh(['firstAudit', 'finalAudit']));

        return $audit->fresh(['answers.item']);
    }

    public function submitForReview(PlanterAudit $audit, User $actor): PlanterAudit
    {
        abort_unless($actor->canWorkAuditRound($audit->round), 403);
        abort_unless($audit->is_current, 422, 'Only the current audit attempt can be submitted.');

        $audit->loadMissing('answers');
        $progress = $audit->checklistProgress();

        if ($progress['total'] === 0 || $progress['answered'] < $progress['total']) {
            throw new InvalidArgumentException('Complete every checklist item before submitting for review.');
        }

        $audit->update([
            'status' => PlanterAudit::STATUS_IN_REVIEW,
            'assigned_to' => $audit->assigned_to ?: $actor->id,
        ]);

        $this->syncPlanterSummary($audit->planter->fresh(['firstAudit', 'finalAudit']));

        return $audit->refresh();
    }

    public function completeRound(
        PlanterAudit $audit,
        User $actor,
        string $status,
        ?string $notes = null,
    ): PlanterAudit {
        abort_unless($actor->canWorkAuditRound($audit->round), 403);
        abort_unless($audit->is_current, 422, 'Only the current audit attempt can be completed.');

        if (! in_array($status, [
            PlanterAudit::STATUS_PASSED,
            PlanterAudit::STATUS_CONDITIONAL,
            PlanterAudit::STATUS_FAILED,
        ], true)) {
            throw new InvalidArgumentException('Invalid audit outcome.');
        }

        $audit->loadMissing('answers');
        $progress = $audit->checklistProgress();

        if ($progress['total'] === 0 || $progress['answered'] < $progress['total']) {
            throw new InvalidArgumentException('Complete every checklist item before completing the audit.');
        }

        $audit->update([
            'status' => $status,
            'outcome_notes' => filled($notes) ? $notes : null,
            'completed_by' => $actor->id,
            'completed_at' => now(),
            'assigned_to' => $audit->assigned_to ?: $actor->id,
        ]);

        $this->syncPlanterSummary($audit->planter->fresh(['firstAudit', 'finalAudit']));

        return $audit->refresh();
    }

    public function reopen(PlanterAudit $audit, User $actor): PlanterAudit
    {
        abort_unless($actor->canDispatchAudits() || $actor->canWorkAuditRound($audit->round), 403);
        abort_unless($audit->is_current, 422, 'Only the current audit attempt can be reopened.');
        abort_unless($audit->isComplete(), 422, 'Only completed audits can be reopened.');

        $planter = $audit->planter()->with(['firstAudit', 'finalAudit'])->firstOrFail();

        if ($audit->round === PlanterAudit::ROUND_FIRST && $planter->finalAudit) {
            throw new InvalidArgumentException('Reopen Final Audit first before restarting First Audit.');
        }

        return DB::transaction(function () use ($audit, $actor, $planter) {
            $audit->update(['is_current' => false]);

            $nextAttempt = (int) PlanterAudit::query()
                ->where('planter_id', $audit->planter_id)
                ->where('round', $audit->round)
                ->max('attempt_number') + 1;

            $fresh = PlanterAudit::query()->create([
                'planter_id' => $audit->planter_id,
                'round' => $audit->round,
                'attempt_number' => $nextAttempt,
                'is_current' => true,
                'status' => PlanterAudit::STATUS_QUEUED,
                'sent_by' => $actor->id,
                'sent_at' => now(),
            ]);

            $this->seedChecklistAnswers($fresh);

            if ($audit->round === PlanterAudit::ROUND_FINAL) {
                $planter->update([
                    'audit_result_outcome' => null,
                    'audit_result_notes' => null,
                ]);
            }

            $this->syncPlanterSummary($planter->fresh(['firstAudit', 'finalAudit']));

            return $fresh->fresh(['answers']);
        });
    }

    private function seedChecklistAnswers(PlanterAudit $audit): void
    {
        $items = AuditChecklistItem::query()->active()->ordered()->get();

        foreach ($items as $item) {
            PlanterAuditChecklistAnswer::query()->create([
                'planter_audit_id' => $audit->id,
                'audit_checklist_item_id' => $item->id,
                'result' => PlanterAuditChecklistAnswer::RESULT_PENDING,
            ]);
        }
    }

    public function syncPlanterSummary(Planter $planter): void
    {
        $first = $planter->firstAudit;
        $final = $planter->finalAudit;

        $payload = [
            'audit_status_updated_at' => now(),
        ];

        if (! $first) {
            $payload['audit_status'] = Planter::AUDIT_NOT_STARTED;
            $payload['audit_result_outcome'] = null;
            $payload['audit_result_notes'] = null;
        } elseif (! $final) {
            $payload['audit_status'] = match ($first->status) {
                PlanterAudit::STATUS_QUEUED => Planter::AUDIT_OPEN,
                PlanterAudit::STATUS_IN_PROGRESS => Planter::AUDIT_IN_PROGRESS,
                PlanterAudit::STATUS_IN_REVIEW => Planter::AUDIT_IN_REVIEW,
                PlanterAudit::STATUS_PASSED, PlanterAudit::STATUS_CONDITIONAL => Planter::AUDIT_IN_REVIEW,
                PlanterAudit::STATUS_FAILED => Planter::AUDIT_RESULT,
                default => Planter::AUDIT_OPEN,
            };

            if ($first->status === PlanterAudit::STATUS_FAILED) {
                $payload['audit_result_outcome'] = Planter::AUDIT_OUTCOME_NOT_CERTIFIED;
                $payload['audit_result_notes'] = $first->outcome_notes;
            } else {
                $payload['audit_result_outcome'] = null;
                $payload['audit_result_notes'] = null;
            }
        } else {
            $payload['audit_status'] = match ($final->status) {
                PlanterAudit::STATUS_QUEUED => Planter::AUDIT_OPEN,
                PlanterAudit::STATUS_IN_PROGRESS => Planter::AUDIT_IN_PROGRESS,
                PlanterAudit::STATUS_IN_REVIEW => Planter::AUDIT_IN_REVIEW,
                PlanterAudit::STATUS_PASSED => Planter::AUDIT_RESULT,
                PlanterAudit::STATUS_CONDITIONAL => Planter::AUDIT_RESULT,
                PlanterAudit::STATUS_FAILED => Planter::AUDIT_RESULT,
                default => Planter::AUDIT_IN_PROGRESS,
            };

            $payload['audit_result_outcome'] = match ($final->status) {
                PlanterAudit::STATUS_PASSED => Planter::AUDIT_OUTCOME_CERTIFIED,
                PlanterAudit::STATUS_CONDITIONAL => Planter::AUDIT_OUTCOME_CONDITIONAL,
                PlanterAudit::STATUS_FAILED => Planter::AUDIT_OUTCOME_NOT_CERTIFIED,
                default => null,
            };
            $payload['audit_result_notes'] = $final->isComplete() ? $final->outcome_notes : null;
        }

        $planter->update($payload);
    }
}
