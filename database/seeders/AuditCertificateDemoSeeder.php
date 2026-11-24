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
use Illuminate\Support\Facades\Config;

class AuditCertificateDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid flooding Notify.lk while building demo audits/certificates.
        Config::set('services.notify.enabled', false);

        $admin = User::query()->where('email', UserSeeder::ADMIN_EMAIL)->firstOrFail();
        $firstAuditor = User::query()->where('email', 'first.auditor@rrisl.gov.lk')->firstOrFail();
        $secondAuditor = User::query()
            ->whereIn('email', ['second.auditor@rrisl.gov.lk', 'final.auditor@rrisl.gov.lk'])
            ->orderByRaw("email = 'second.auditor@rrisl.gov.lk' desc")
            ->firstOrFail();
        $staff = User::query()->where('email', 'rrisl@rrisl.gov.lk')->first()
            ?? User::query()->where('email', 'staff@rrisl.gov.lk')->firstOrFail();

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
            'first_queued' => 8,
            'first_in_progress' => 10,
            'first_failed' => 6,
            'first_passed_ready' => 8,
            'final_queued' => 8,
            'final_in_progress' => 10,
            'final_failed' => 5,
            // Ready for certificate issue (Second Audit passed, no cert yet)
            'final_passed_ready_cert' => 15,
            'final_passed_cert' => 10,
            'final_conditional_cert' => 5,
            'final_passed_revoked' => 4,
        ];

        $offset = 0;

        foreach ($chunks as $scenario => $count) {
            $slice = $approved->slice($offset, $count)->values();
            $offset += $count;

            foreach ($slice as $planter) {
                match ($scenario) {
                    'first_queued' => $this->seedFirstQueued($audits, $planter, $staff),
                    'first_in_progress' => $this->seedFirstInProgress($audits, $planter, $staff, $firstAuditor),
                    'first_failed' => $this->seedFirstFailed($audits, $planter, $staff, $firstAuditor),
                    'first_passed_ready' => $this->seedFirstPassed($audits, $planter, $staff, $firstAuditor),
                    'final_queued' => $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor),
                    'final_in_progress' => $this->seedFinalInProgress($audits, $planter, $staff, $firstAuditor, $secondAuditor),
                    'final_failed' => $this->seedFinalFailed($audits, $planter, $staff, $firstAuditor, $secondAuditor),
                    'final_passed_ready_cert' => $this->seedFinalPassedReadyForCertificate(
                        $audits,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $secondAuditor,
                        PlanterAudit::STATUS_PASSED,
                    ),
                    'final_passed_cert' => $this->seedFinalPassedWithCertificate(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $secondAuditor,
                        $admin,
                        Certificate::OUTCOME_CERTIFIED,
                    ),
                    'final_conditional_cert' => $this->seedFinalPassedWithCertificate(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $secondAuditor,
                        $admin,
                        Certificate::OUTCOME_CONDITIONAL,
                    ),
                    'final_passed_revoked' => $this->seedFinalPassedRevoked(
                        $audits,
                        $certificates,
                        $planter,
                        $staff,
                        $firstAuditor,
                        $secondAuditor,
                        $admin,
                    ),
                    default => null,
                };
            }
        }

        $this->signCompletedAudits();

        $pending = Planter::query()->where('status', Planter::STATUS_PENDING)->count();
        $readyForCert = Planter::query()
            ->where('status', Planter::STATUS_APPROVED)
            ->whereHas('finalAudit', function ($query) {
                $query->where('is_current', true)
                    ->whereIn('status', [PlanterAudit::STATUS_PASSED, PlanterAudit::STATUS_CONDITIONAL]);
            })
            ->whereDoesntHave('currentCertificate')
            ->count();

        $this->command?->info(sprintf(
            'Seeded audit/certificate samples for %d approved planters (%d left not started). Pending approvals: %d. Ready for certificate: %d.',
            min($offset, $approved->count()),
            max(0, $approved->count() - $offset),
            $pending,
            $readyForCert,
        ));
    }

    /**
     * Give every completed demo audit a placeholder handwritten-style signature.
     */
    private function signCompletedAudits(): void
    {
        PlanterAudit::query()
            ->whereNull('signature')
            ->whereNotNull('completed_by')
            ->with('completer')
            ->each(function (PlanterAudit $audit) {
                $name = $audit->completer?->name ?? 'Auditor';
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="100" viewBox="0 0 320 100">'
                    .'<path d="M12 70 C 40 10, 70 90, 100 40 S 150 20, 170 60 S 220 85, 250 35 S 290 55, 308 45" '
                    .'fill="none" stroke="#14301f" stroke-width="3" stroke-linecap="round"/></svg>';

                $audit->forceFill([
                    'signature' => 'data:image/svg+xml;base64,'.base64_encode($svg),
                    'signed_by_name' => $name,
                    'signed_at' => $audit->completed_at,
                ])->save();
            });
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
        User $secondAuditor,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $secondAuditor);
        $this->fillAnswers($final->fresh(['answers']), $secondAuditor, half: true);
    }

    private function seedFinalFailed(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $secondAuditor,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $secondAuditor);
        $this->fillAnswers($final->fresh(['answers']), $secondAuditor, fail: true);
        $audits->completeRound(
            $final->fresh(['answers']),
            $secondAuditor,
            PlanterAudit::STATUS_FAILED,
            'Demo: Second Audit failed.',
        );
    }

    private function seedFinalPassedReadyForCertificate(
        PlanterAuditService $audits,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $secondAuditor,
        string $status = PlanterAudit::STATUS_PASSED,
    ): void {
        $this->seedFinalQueued($audits, $planter, $staff, $firstAuditor);
        $final = $planter->fresh(['finalAudit'])->finalAudit;
        $audits->start($final, $secondAuditor);
        $this->fillAnswers($final->fresh(['answers']), $secondAuditor);
        $audits->completeRound(
            $final->fresh(['answers']),
            $secondAuditor,
            $status,
            $status === PlanterAudit::STATUS_CONDITIONAL
                ? 'Demo: Second Audit conditional — ready for certificate.'
                : 'Demo: Second Audit passed — ready for certificate.',
        );
    }

    private function seedFinalPassedWithCertificate(
        PlanterAuditService $audits,
        CertificateService $certificates,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $secondAuditor,
        User $admin,
        string $outcome,
    ): void {
        $status = $outcome === Certificate::OUTCOME_CONDITIONAL
            ? PlanterAudit::STATUS_CONDITIONAL
            : PlanterAudit::STATUS_PASSED;

        $this->seedFinalPassedReadyForCertificate(
            $audits,
            $planter,
            $staff,
            $firstAuditor,
            $secondAuditor,
            $status,
        );

        $certificates->issue($planter->fresh(['finalAudit', 'currentCertificate']), $admin, 'Demo certificate');
    }

    private function seedFinalPassedRevoked(
        PlanterAuditService $audits,
        CertificateService $certificates,
        Planter $planter,
        User $staff,
        User $firstAuditor,
        User $secondAuditor,
        User $admin,
    ): void {
        $this->seedFinalPassedWithCertificate(
            $audits,
            $certificates,
            $planter,
            $staff,
            $firstAuditor,
            $secondAuditor,
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
                    : ($half ? 'Demo in-progress checklist note.' : null),
                'answered_by' => $actor->id,
                'answered_at' => now()->subDays(max(1, 10 - $index)),
            ]);
        }
    }
}
