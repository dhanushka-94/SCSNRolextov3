<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\PlanterAuditChecklistAnswer;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\PlanterAuditService;
use Illuminate\Database\Seeder;

class AuditCertificateDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', UserSeeder::ADMIN_EMAIL)->firstOrFail();
        $firstAuditor = User::query()->where('email', 'first.auditor@rrisl.gov.lk')->firstOrFail();
        $finalAuditor = User::query()->where('email', 'final.auditor@rrisl.gov.lk')->firstOrFail();
        $staff = User::query()->where('email', 'staff@rrisl.gov.lk')->firstOrFail();

        $audits = app(PlanterAuditService::class);
        $certificates = app(CertificateService::class);

        $approved = Planter::query()
            ->where('status', Planter::STATUS_APPROVED)
            ->whereNotNull('password')
            ->orderBy('id')
            ->get();

        if ($approved->isEmpty()) {
            $this->command?->warn('No approved planters with passwords to seed audits/certificates.');

            return;
        }

        $chunks = [
            'first_queued' => 10,
            'first_in_progress' => 10,
            'first_failed' => 8,
            'first_passed_ready' => 10,
            'final_queued' => 8,
            'final_in_progress' => 8,
            'final_failed' => 5,
            'final_passed_cert' => 12,
            'final_conditional_cert' => 5,
            'final_passed_revoked' => 4,
        ];

        $offset = 0;

        foreach ($chunks as $scenario => $count) {
            $slice = $approved->slice($offset, $count)->values();
            $offset += $count;

            foreach ($slice as $index => $planter) {
                match ($scenario) {
                    'first_queued' => $this->seedFirstQueued($audits, $planter, $staff),
                    'first_in_progress' => $this->seedFirstInProgress($audits, $planter, $staff, $firstAuditor),
                    'first_failed' => $this->seedFirstFailed($audits, $planter, $staff, $firstAuditor),
                    'first_passed_ready' => $this->seedFirstPassed($audits, $planter, $staff, $firstAuditor),
                    'final_queued' => $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor),
                    'final_in_progress' => $this->seedFinalInProgress($audits, $planter, $staff, $firstAuditor, $finalAuditor),
                    'final_failed' => $this->seedFinalFailed($audits, $planter, $staff, $firstAuditor, $finalAuditor),
                    'final_passed_cert' => $this->seedFinalPassedWithCertificate(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $finalAuditor,
                        $admin,
                        Certificate::OUTCOME_CERTIFIED,
                    ),
                    'final_conditional_cert' => $this->seedFinalPassedWithCertificate(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $finalAuditor,
                        $admin,
                        Certificate::OUTCOME_CONDITIONAL,
                    ),
                    'final_passed_revoked' => $this->seedFinalPassedRevoked(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $finalAuditor,
                        $admin,
                    ),
                    default => null,
                };
            }
        }

        $this->command?->info(sprintf(
            'Seeded audit/certificate samples for %d approved planters (%d left not started).',
            min($offset, $approved->count()),
            max(0, $approved->count() - $offset)
        ));
    }

    private function seedFirstQueued(PlanterAuditService $audits, Planter $planter, User $staff): void
    {
        $audits->sendToFirstAudit($planter, $staff);
    }

    private function seedFirstInProgress(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
    ): void {
        $audit = $audits->sendToFirstAudit($planter, $staff);
        $audits->start($audit, $firstAuditor);
        $this->fillAnswers($audit->fresh(['answers']), $firstAuditor, half: true);
    }

    private function seedFirstFailed(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
    ): void {
        $audit = $audits->sendToFirstAudit($planter, $staff);
        $audits->start($audit, $firstAuditor);
        $this->fillAnswers($audit->fresh(['answers']), $firstAuditor, fail: true);
        $audits->completeRound(
            $audit->fresh(['answers']),
            $firstAuditor,
            PlanterAudit::STATUS_FAILED,
            'Demo: First Audit failed — corrective actions required.',
        );
    }

    private function seedFirstPassed(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
    ): void {
        $audit = $audits->sendToFirstAudit($planter, $staff);
        $audits->start($audit, $firstAuditor);
        $this->fillAnswers($audit->fresh(['answers']), $firstAuditor);
        $audits->completeRound(
            $audit->fresh(['answers']),
            $firstAuditor,
            PlanterAudit::STATUS_PASSED,
            'Demo: First Audit passed.',
        );
    }

    private function seedFinalQueued(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
    ): void {
        $this->seedFirstPassed($audits, $planter, $staff, $firstAuditor);
        $audits->sendToFinalAudit($planter->fresh(['firstAudit']), $staff);
    }

    private function seedFinalInProgress(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $finalAuditor,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $finalAuditor);
        $this->fillAnswers($final->fresh(['answers']), $finalAuditor, half: true);
    }

    private function seedFinalFailed(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $finalAuditor,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $finalAuditor);
        $this->fillAnswers($final->fresh(['answers']), $finalAuditor, fail: true);
        $audits->completeRound(
            $final->fresh(['answers']),
            $finalAuditor,
            PlanterAudit::STATUS_FAILED,
            'Demo: Final Audit failed.',
        );
    }

    private function seedFinalPassedWithCertificate(
        PlanterAuditService $audits,
        CertificateService $certificates,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $finalAuditor,
        User $admin,
        string $outcome,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $finalAuditor);
        $this->fillAnswers($final->fresh(['answers']), $finalAuditor);
        $status = $outcome === Certificate::OUTCOME_CONDITIONAL
            ? PlanterAudit::STATUS_CONDITIONAL
            : PlanterAudit::STATUS_PASSED;
        $audits->completeRound(
            $final->fresh(['answers']),
            $finalAuditor,
            $status,
            $outcome === Certificate::OUTCOME_CONDITIONAL
                ? 'Demo: Final Audit conditional pass.'
                : 'Demo: Final Audit passed.',
        );
        $certificates->issue($planter->fresh(['finalAudit', 'currentCertificate']), $admin, 'Demo certificate');
    }

    private function seedFinalPassedRevoked(
        PlanterAuditService $audits,
        CertificateService $certificates,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $finalAuditor,
        User $admin,
    ): void {
        $this->seedFinalPassedWithCertificate(
            $audits,
            $certificates,
            $planter,
            $staff,
            $firstAuditor,
            $finalAuditor,
            $admin,
            Certificate::OUTCOME_CERTIFIED,
        );

        $certificate = $planter->fresh(['currentCertificate'])->currentCertificate;
        if ($certificate) {
            $certificates->revoke($certificate, $admin, 'Demo: certificate revoked for sample data.');
        }
    }

    private function fillAnswers(
        PlanterAudit $audit,
        User $actor,
        bool $half = false,
        bool $fail = false,
    ): void {
        $answers = $audit->answers()->with('item')->orderBy('id')->get();
        $total = $answers->count();
        $limit = $half ? (int) max(1, floor($total / 2)) : $total;

        foreach ($answers->values() as $index => $answer) {
            if ($index >= $limit) {
                break;
            }

            $result = PlanterAuditChecklistAnswer::RESULT_PASS;

            if ($fail && $index % 4 === 0) {
                $result = PlanterAuditChecklistAnswer::RESULT_FAIL;
            } elseif ($index % 7 === 0) {
                $result = PlanterAuditChecklistAnswer::RESULT_NA;
            }

            $answer->update([
                'result' => $result,
                'comment' => $result === PlanterAuditChecklistAnswer::RESULT_FAIL
                    ? 'Demo finding — needs follow-up.'
                    : null,
                'answered_by' => $actor->id,
                'answered_at' => now()->subDays(max(1, 10 - $index)),
            ]);
        }
    }
}
