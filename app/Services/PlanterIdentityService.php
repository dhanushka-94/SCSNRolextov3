<?php

namespace App\Services;

use App\Models\District;
use App\Models\Planter;
use App\Models\RdoDivision;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanterIdentityService
{
    public const ID_PREFIX = 'RUB/SUS';

    public const SEQUENCE_PAD = 5;

    public function approve(Planter $planter, int $approvedBy): Planter
    {
        return DB::transaction(function () use ($planter, $approvedBy) {
            $planter = Planter::query()->lockForUpdate()->findOrFail($planter->id);

            $this->issueRegistrationNumberLocked($planter);

            $planter->fill([
                'status' => Planter::STATUS_APPROVED,
                'audit_status' => Planter::AUDIT_NOT_STARTED,
                'audit_result_outcome' => null,
                'audit_result_notes' => null,
                'audit_status_updated_at' => now(),
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $planter->save();

            return $planter->refresh();
        });
    }

    public function reject(Planter $planter, int $rejectedBy, string $reason): Planter
    {
        return DB::transaction(function () use ($planter, $rejectedBy, $reason) {
            $planter = Planter::query()->lockForUpdate()->findOrFail($planter->id);

            $this->issueRegistrationNumberLocked($planter);

            $planter->fill([
                'status' => Planter::STATUS_REJECTED,
                'approved_by' => $rejectedBy,
                'approved_at' => now(),
                'rejection_reason' => $reason,
                'audit_status' => Planter::AUDIT_NOT_STARTED,
                'audit_result_outcome' => null,
                'audit_result_notes' => null,
                'audit_status_updated_at' => null,
            ]);

            $planter->save();

            return $planter->refresh();
        });
    }

    public function ensureIssued(Planter $planter): Planter
    {
        if ((! $planter->isApproved() && ! $planter->isRejected()) || filled($planter->identification_number)) {
            return $planter;
        }

        return DB::transaction(function () use ($planter) {
            $planter = Planter::query()->lockForUpdate()->findOrFail($planter->id);

            if (blank($planter->identification_number) && ($planter->isApproved() || $planter->isRejected())) {
                $this->issueRegistrationNumberLocked($planter);
                $planter->save();
            }

            return $planter->refresh();
        });
    }

    public function generateIdentificationNumber(Planter $planter): string
    {
        [$districtCode, $divisionCode, $divisionId] = $this->resolveLocationCodes($planter);

        $prefix = self::ID_PREFIX.'/'.$districtCode.'/'.$divisionCode.'/';

        $query = Planter::query()
            ->where('identification_number', 'like', $prefix.'%')
            ->lockForUpdate();

        if ($divisionId) {
            $query->where('rdo_division_id', $divisionId);
        }

        $latest = $query
            ->orderByDesc('identification_number')
            ->value('identification_number');

        $sequence = 1;

        if ($latest) {
            $tail = substr($latest, strrpos($latest, '/') + 1);
            $sequence = ((int) $tail) + 1;
        }

        return $prefix.str_pad((string) $sequence, self::SEQUENCE_PAD, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function assignTemporaryId(array $data): array
    {
        $data['temporary_id'] = Planter::generateTemporaryId();
        $data['identification_number'] = null;

        return $data;
    }

    public function qrPayload(Planter $planter): string
    {
        return $planter->identification_number ?: $planter->temporary_id;
    }

    public function png(Planter $planter, int $size = 480): string
    {
        return (new Builder(
            writer: new PngWriter(),
            data: $this->qrPayload($planter),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 16,
        ))->build()->getString();
    }

    public function svg(Planter $planter, int $size = 1200): string
    {
        return (new Builder(
            writer: new SvgWriter(),
            data: $this->qrPayload($planter),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 16,
        ))->build()->getString();
    }

    private function issueRegistrationNumberLocked(Planter $planter): void
    {
        if (filled($planter->identification_number)) {
            return;
        }

        $planter->identification_number = $this->generateIdentificationNumber($planter);
    }

    /**
     * @return array{0: string, 1: string, 2: int|null}
     */
    private function resolveLocationCodes(Planter $planter): array
    {
        $district = null;
        $division = null;

        if ($planter->rdo_division_id) {
            $division = RdoDivision::query()->with('district')->find($planter->rdo_division_id);
            $district = $division?->district;
        }

        if (! $district && $planter->district_id) {
            $district = District::query()->find($planter->district_id);
        }

        if (! $district && filled($planter->district)) {
            $district = District::query()->where('name', $planter->district)->first();
        }

        if (! $division && $district && filled($planter->rdd_division)) {
            $division = RdoDivision::query()
                ->where('district_id', $district->id)
                ->where('name', $planter->rdd_division)
                ->first();
        }

        $districtCode = $district?->code;
        $divisionCode = $division?->code;

        if (blank($districtCode) || blank($divisionCode)) {
            throw new InvalidArgumentException(
                'District and Rubber Development Officer division codes are required to issue a registration number.'
            );
        }

        return [$districtCode, $divisionCode, $division?->id];
    }
}
