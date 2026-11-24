<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\District;
use App\Models\Planter;
use App\Models\RdoDivision;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanterIdentityService
{
    public const ID_PREFIX = 'RUB/SUS';

    public const SEQUENCE_PAD = 5;

    public function approve(Planter $planter, int $approvedBy): Planter
    {
        $planter = DB::transaction(function () use ($planter, $approvedBy) {
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

        ActivityLog::record('planter.approved', $planter, ['sppnr_id' => $planter->identification_number]);

        app(NotifySmsService::class)->sendRegistrationApproved($planter);

        return $planter;
    }

    public function reject(Planter $planter, int $rejectedBy, string $reason): Planter
    {
        $planter = DB::transaction(function () use ($planter, $rejectedBy, $reason) {
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

        ActivityLog::record('planter.rejected', $planter, ['reason' => $reason]);

        app(NotifySmsService::class)->sendRegistrationRejected($planter);

        return $planter;
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

    /**
     * Tracking QR: the planter's reference number. It is assigned at registration and
     * never changes, so the same printed code works from registration to the end.
     * Staff scan it in the mobile app (GET /api/v1/staff/scan/{reference}).
     */
    public function qrPayload(Planter $planter): string
    {
        return (string) ($planter->temporary_id ?: $planter->identification_number);
    }

    /** Certificate QR: the public verification link of one specific certificate. */
    public function certificateQrPayload(Certificate $certificate): string
    {
        return $certificate->verifyUrl();
    }

    public function png(Planter $planter, int $size = 480): string
    {
        return $this->render(new PngWriter, $this->qrPayload($planter), $size);
    }

    public function svg(Planter $planter, int $size = 1200): string
    {
        return $this->render(new SvgWriter, $this->qrPayload($planter), $size);
    }

    public function certificatePng(Certificate $certificate, int $size = 480): string
    {
        return $this->render(new PngWriter, $this->certificateQrPayload($certificate), $size);
    }

    public function certificateSvg(Certificate $certificate, int $size = 1200): string
    {
        return $this->render(new SvgWriter, $this->certificateQrPayload($certificate), $size);
    }

    /** Finds a planter from a scanned or typed reference (registration reference or SPPNR ID). */
    public function findByReference(string $reference): ?Planter
    {
        $reference = trim($reference);

        if ($reference === '') {
            return null;
        }

        $normalized = strtoupper((string) preg_replace('/\s+/', '', $reference));

        return Planter::query()
            ->where('temporary_id', $normalized)
            ->orWhereRaw('UPPER(REPLACE(identification_number, " ", "")) = ?', [$normalized])
            ->first();
    }

    private function render(WriterInterface $writer, string $data, int $size): string
    {
        return (new Builder(
            writer: $writer,
            data: $data,
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
