<?php

namespace App\Http\Resources\Api;

use App\Models\PlanterAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlanterAudit */
class PlanterAuditResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planter_id' => $this->planter_id,
            'round' => $this->round,
            'round_label' => $this->roundLabel(),
            'attempt_number' => $this->attempt_number,
            'is_current' => (bool) $this->is_current,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'is_open' => $this->isOpen(),
            'is_complete' => $this->isComplete(),
            'outcome_notes' => $this->outcome_notes,
            'is_signed' => $this->isSigned(),
            'signed_by_name' => $this->signed_by_name,
            'signed_at' => $this->signed_at,
            'sent_at' => $this->sent_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee?->name),
            'completer' => $this->whenLoaded('completer', fn () => $this->completer?->name),
            'planter' => $this->whenLoaded('planter', fn () => [
                'id' => $this->planter->id,
                'name' => $this->planter->name,
                'identification_number' => $this->planter->identification_number,
                'temporary_id' => $this->planter->temporary_id,
                'district' => $this->planter->district,
                'phone' => $this->planter->phone,
            ]),
        ];
    }
}
