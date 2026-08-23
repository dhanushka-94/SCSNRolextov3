<?php

namespace App\Services;

use App\Models\Planter;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\DB;

class PlanterIdentityService
{
    public function approve(Planter $planter, int $approvedBy): Planter
    {
        return DB::transaction(function () use ($planter, $approvedBy) {
            $planter = Planter::query()->lockForUpdate()->findOrFail($planter->id);

            if (blank($planter->identification_number)) {
                $planter->identification_number = $this->generateIdentificationNumber($planter);
            }

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

    public function ensureIssued(Planter $planter): Planter
    {
        if (! $planter->isApproved() || filled($planter->identification_number)) {
            return $planter;
        }

        return DB::transaction(function () use ($planter) {
            $planter = Planter::query()->lockForUpdate()->findOrFail($planter->id);

            if (blank($planter->identification_number)) {
                $planter->identification_number = $this->generateIdentificationNumber($planter);
                $planter->save();
            }

            return $planter->refresh();
        });
    }

    public function generateIdentificationNumber(Planter $planter): string
    {
        $code = Planter::districtCode($planter->district);
        $year = (string) now()->year;
        $prefix = 'SCSNR/Ad/'.$code.'/'.$year.'/';

        $latest = Planter::query()
            ->where('identification_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('identification_number')
            ->value('identification_number');

        $sequence = $latest ? ((int) substr($latest, strrpos($latest, '/') + 1)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function assignRegistrationNumbers(array $data): array
    {
        $number = $this->generateIdentificationNumber(new Planter($data));
        $data['identification_number'] = $number;
        // Keep legacy column in sync for existing schema constraints.
        $data['temporary_id'] = $number;

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
}
