<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Planter;
use App\Models\User;
use App\Support\PlanterLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Staff create / update / delete for registry entries, shared by the mobile API.
 * Mirrors the web admin behaviour so both channels stay in step.
 */
class PlanterRegistryService
{
    public function __construct(private PlanterIdentityService $identity) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromStaff(array $data, User $actor): Planter
    {
        return DB::transaction(function () use ($data, $actor) {
            $payload = PlanterLocation::hydrateNames($data);
            $payload['registration_type'] = Planter::TYPE_ONLINE;

            if (blank($payload['password'] ?? null)) {
                unset($payload['password']);
            }

            if (($payload['status'] ?? null) !== Planter::STATUS_REJECTED) {
                $payload['rejection_reason'] = null;
            }

            $payload = $this->identity->assignTemporaryId($payload);
            $planter = Planter::query()->create($payload);

            if ($planter->status === Planter::STATUS_APPROVED) {
                return $this->identity->approve($planter, $actor->id);
            }

            if ($planter->status === Planter::STATUS_REJECTED) {
                return $this->identity->reject(
                    $planter,
                    $actor->id,
                    $payload['rejection_reason'] ?? 'Rejected by administrator.',
                );
            }

            return $planter;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateFromStaff(Planter $planter, array $data, User $actor): Planter
    {
        $payload = PlanterLocation::hydrateNames($data);

        if (blank($payload['password'] ?? null)) {
            unset($payload['password']);
        }

        if (($payload['status'] ?? null) === Planter::STATUS_PENDING) {
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
            $payload['rejection_reason'] = null;
        }

        if (($payload['status'] ?? null) !== Planter::STATUS_REJECTED) {
            $payload['rejection_reason'] = null;
        }

        $wasApproved = $planter->isApproved();
        $wasRejected = $planter->isRejected();
        $planter->update($payload);

        if ($planter->status === Planter::STATUS_APPROVED && ! $wasApproved) {
            return $this->identity->approve($planter, $actor->id);
        }

        if ($planter->status === Planter::STATUS_REJECTED && ! $wasRejected) {
            return $this->identity->reject(
                $planter,
                $actor->id,
                $payload['rejection_reason'] ?? 'Rejected by administrator.',
            );
        }

        return $planter->refresh();
    }

    public function deleteWithDocuments(Planter $planter): void
    {
        foreach (['application_document', 'prior_certificate_document'] as $column) {
            if (filled($planter->{$column})) {
                Storage::disk('local')->delete($planter->{$column});
            }
        }

        ActivityLog::record('planter.deleted', $planter, ['sppnr_id' => $planter->identification_number, 'status' => $planter->status]);

        $planter->delete();
    }
}
