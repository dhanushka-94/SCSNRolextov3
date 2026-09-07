<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CertificateService
{
    public const NUMBER_PREFIX = 'SCSNR/CERT';

    public const SEQUENCE_PAD = 5;

    public function canIssue(Planter $planter): bool
    {
        if (! $planter->isApproved()) {
            return false;
        }

        $current = $planter->relationLoaded('currentCertificate')
            ? $planter->currentCertificate
            : $planter->currentCertificate()->first();

        if ($current?->isIssued()) {
            return false;
        }

        $final = $planter->relationLoaded('finalAudit')
            ? $planter->finalAudit
            : $planter->finalAudit()->first();

        return $final
            && $final->is_current
            && in_array($final->status, [
                PlanterAudit::STATUS_PASSED,
                PlanterAudit::STATUS_CONDITIONAL,
            ], true);
    }

    public function issue(Planter $planter, User $actor, ?string $notes = null): Certificate
    {
        abort_unless($actor->canManageCertificates(), 403);

        $planter->loadMissing('finalAudit');

        if (! $this->canIssue($planter)) {
            throw new InvalidArgumentException(
                'Certificate can only be issued after Final Audit is passed or conditional, and when no active certificate exists.'
            );
        }

        $outcome = $planter->finalAudit->status === PlanterAudit::STATUS_CONDITIONAL
            ? Certificate::OUTCOME_CONDITIONAL
            : Certificate::OUTCOME_CERTIFIED;

        return DB::transaction(function () use ($planter, $actor, $notes, $outcome) {
            Certificate::query()
                ->where('planter_id', $planter->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            return Certificate::query()->create([
                'planter_id' => $planter->id,
                'certificate_number' => $this->nextCertificateNumber(),
                'public_token' => (string) Str::uuid(),
                'status' => Certificate::STATUS_ISSUED,
                'outcome' => $outcome,
                'is_current' => true,
                'issued_by' => $actor->id,
                'issued_at' => now(),
                'notes' => filled($notes) ? trim($notes) : null,
            ]);
        });
    }

    public function revoke(Certificate $certificate, User $actor, string $reason): Certificate
    {
        abort_unless($actor->canManageCertificates(), 403);
        abort_unless($certificate->isIssued(), 422, 'Only issued certificates can be revoked.');

        $certificate->update([
            'status' => Certificate::STATUS_REVOKED,
            'revoked_by' => $actor->id,
            'revoked_at' => now(),
            'revoke_reason' => trim($reason),
            'is_current' => false,
        ]);

        return $certificate->refresh();
    }

    private function nextCertificateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = self::NUMBER_PREFIX.'/'.$year.'/';

        $latest = Certificate::query()
            ->where('certificate_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('certificate_number')
            ->value('certificate_number');

        $sequence = 1;

        if ($latest) {
            $tail = substr($latest, strrpos($latest, '/') + 1);
            $sequence = ((int) $tail) + 1;
        }

        return $prefix.str_pad((string) $sequence, self::SEQUENCE_PAD, '0', STR_PAD_LEFT);
    }
}
