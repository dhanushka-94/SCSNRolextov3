<?php

namespace App\Http\Requests\Planters;

use App\Models\Planter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait ValidatesPlanterApplicationFields
{
    /**
     * @return array<string, mixed>
     */
    protected function applicationFieldRules(bool $requireCoreApplication = false): array
    {
        $presence = $requireCoreApplication ? 'required' : 'nullable';

        return [
            'rdd_division' => [$presence, 'string', 'max:120'],
            'farm_name' => [$presence, 'string', 'max:160'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'fax' => ['nullable', 'string', 'max:30'],
            'business_type' => [$presence, Rule::in(array_keys(Planter::businessTypes()))],
            'already_certified' => [$presence, 'boolean'],
            'certification_standard' => ['nullable', 'required_if:already_certified,1', 'string', 'max:190'],
            'certification_rejected_or_suspended' => [$presence, 'boolean'],
            'certification_issue_reason' => ['nullable', 'required_if:certification_rejected_or_suspended,1', 'string', 'max:1000'],
            'crops_products' => [$requireCoreApplication ? 'required' : 'nullable', 'array', 'max:10'],
            'crops_products.*' => ['nullable', 'string', 'max:120'],
            'aware_of_certification' => [$presence, 'boolean'],
            'has_certification_leaflet' => [$presence, 'boolean'],
            'processes_rubber_on_farm' => [$presence, 'boolean'],
            'has_process_plan' => [
                'nullable',
                Rule::requiredIf(fn () => (string) $this->input('processes_rubber_on_farm') === '1'),
                Rule::in(array_keys(Planter::processPlanOptions())),
            ],
            'group_name' => ['nullable', 'string', 'max:160'],
            'group_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareApplicationFields(): void
    {
        $crops = collect($this->input('crops_products', []))
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'crops_products' => $crops === [] ? null : $crops,
            'whatsapp' => $this->filled('whatsapp') ? $this->whatsapp : null,
            'fax' => $this->filled('fax') ? $this->fax : null,
            'rdd_division' => $this->filled('rdd_division') ? $this->rdd_division : null,
            'farm_name' => $this->filled('farm_name') ? $this->farm_name : null,
            'business_type' => $this->filled('business_type') ? $this->business_type : null,
            'certification_standard' => $this->filled('certification_standard') ? $this->certification_standard : null,
            'certification_issue_reason' => $this->filled('certification_issue_reason') ? $this->certification_issue_reason : null,
            'group_name' => $this->filled('group_name') ? $this->group_name : null,
            'group_address' => $this->filled('group_address') ? $this->group_address : null,
            'has_process_plan' => $this->filled('has_process_plan') ? $this->has_process_plan : null,
            'already_certified' => $this->has('already_certified') ? $this->already_certified : null,
            'certification_rejected_or_suspended' => $this->has('certification_rejected_or_suspended')
                ? $this->certification_rejected_or_suspended
                : null,
            'aware_of_certification' => $this->has('aware_of_certification') ? $this->aware_of_certification : null,
            'has_certification_leaflet' => $this->has('has_certification_leaflet') ? $this->has_certification_leaflet : null,
            'processes_rubber_on_farm' => $this->has('processes_rubber_on_farm') ? $this->processes_rubber_on_farm : null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function applicationFieldAttributes(): array
    {
        return [
            'rdd_division' => 'RDD officer division',
            'farm_name' => 'farm name',
            'business_type' => 'nature of business',
            'already_certified' => 'existing certification',
            'certification_standard' => 'certification standard',
            'certification_rejected_or_suspended' => 'rejection or suspension status',
            'certification_issue_reason' => 'reason',
            'crops_products' => 'products for certification',
            'aware_of_certification' => 'certification awareness',
            'has_certification_leaflet' => 'information leaflet',
            'processes_rubber_on_farm' => 'on-farm processing',
            'has_process_plan' => 'process plan',
            'group_name' => 'plantation company / group name',
            'group_address' => 'group address',
        ];
    }
}
