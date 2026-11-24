<?php

namespace App\Http\Resources\Api;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Certificate */
class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planter_id' => $this->planter_id,
            'certificate_number' => $this->certificate_number,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'outcome' => $this->outcome,
            'outcome_label' => $this->outcomeLabel(),
            'is_current' => (bool) $this->is_current,
            'issued_at' => $this->issued_at,
            'revoked_at' => $this->revoked_at,
            'revoke_reason' => $this->revoke_reason,
            'verify_url' => $this->verifyUrl(),
            'planter' => $this->whenLoaded('planter', fn () => [
                'id' => $this->planter->id,
                'name' => $this->planter->name,
                'identification_number' => $this->planter->identification_number,
                'district' => $this->planter->district,
            ]),
        ];
    }
}
