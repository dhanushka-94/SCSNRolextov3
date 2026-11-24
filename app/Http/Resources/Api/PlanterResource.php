<?php

namespace App\Http\Resources\Api;

use App\Models\Planter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Planter */
class PlanterResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'temporary_id' => $this->temporary_id,
            'identification_number' => $this->identification_number,
            'status' => $this->status,
            'status_label' => Planter::statuses()[$this->status] ?? $this->status,
            'registration_type' => $this->registration_type,
            'name' => $this->name,
            'nic' => $this->nic,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'fax' => $this->fax,
            'district_id' => $this->district_id,
            'district' => $this->district,
            'rdo_division_id' => $this->rdo_division_id,
            'rdd_division' => $this->rdd_division,
            'farm_name' => $this->farm_name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'has_map_pin' => $this->hasMapPin(),
            'business_type' => $this->business_type,
            'business_type_label' => $this->businessTypeLabel(),
            'registration_category' => $this->registration_category,
            'already_certified' => (bool) $this->already_certified,
            'certification_standard' => $this->certification_standard,
            'certification_rejected_or_suspended' => (bool) $this->certification_rejected_or_suspended,
            'certification_issue_reason' => $this->certification_issue_reason,
            'crops_products' => $this->crops_products ?? [],
            'aware_of_certification' => (bool) $this->aware_of_certification,
            'has_certification_leaflet' => (bool) $this->has_certification_leaflet,
            'processes_rubber_on_farm' => (bool) $this->processes_rubber_on_farm,
            'has_process_plan' => $this->has_process_plan,
            'group_name' => $this->group_name,
            'group_address' => $this->group_address,
            'rejection_reason' => $this->rejection_reason,
            'audit_status' => $this->audit_status,
            'audit_result_outcome' => $this->audit_result_outcome,
            'audit_result_label' => Planter::auditResultOutcomes()[$this->audit_result_outcome] ?? null,
            'audit_result_notes' => $this->audit_result_notes,
            'has_application_document' => $this->hasApplicationDocument(),
            'has_prior_certificate_document' => $this->hasPriorCertificateDocument(),
            'approved_at' => $this->approved_at,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
        ];
    }
}
