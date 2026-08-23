<?php

namespace App\Http\Requests\Planters;

use App\Http\Requests\Concerns\ProtectsRegistrationFromSpam;
use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPlanterRequest extends FormRequest
{
    use ProtectsRegistrationFromSpam;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareSpamProtection();

        $crops = collect($this->input('crops_products', []))
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'crops_products' => $crops,
            'whatsapp' => $this->filled('whatsapp') ? $this->whatsapp : null,
            'fax' => $this->filled('fax') ? $this->fax : null,
            'certification_standard' => $this->filled('certification_standard') ? $this->certification_standard : null,
            'certification_issue_reason' => $this->filled('certification_issue_reason') ? $this->certification_issue_reason : null,
            'group_name' => $this->filled('group_name') ? $this->group_name : null,
            'group_address' => $this->filled('group_address') ? $this->group_address : null,
            'has_process_plan' => $this->filled('has_process_plan') ? $this->has_process_plan : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'nic' => ['required', 'string', 'max:20', 'unique:planters,nic'],
            'rdd_division' => ['required', 'string', 'max:120'],
            'district' => ['required', Rule::in(Planter::districts())],
            'farm_name' => ['required', 'string', 'max:160'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'fax' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:planters,email'],
            'business_type' => ['required', Rule::in(array_keys(Planter::businessTypes()))],
            'already_certified' => ['required', 'boolean'],
            'certification_standard' => ['nullable', 'required_if:already_certified,1', 'string', 'max:190'],
            'prior_certificate_document' => ['nullable', 'required_if:already_certified,1', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'certification_rejected_or_suspended' => ['required', 'boolean'],
            'certification_issue_reason' => ['nullable', 'required_if:certification_rejected_or_suspended,1', 'string', 'max:1000'],
            'crops_products' => ['required', 'array', 'min:1', 'max:10'],
            'crops_products.*' => ['required', 'string', 'max:120'],
            'aware_of_certification' => ['required', 'boolean'],
            'has_certification_leaflet' => ['required', 'boolean'],
            'processes_rubber_on_farm' => ['required', 'boolean'],
            'has_process_plan' => [
                'nullable',
                Rule::requiredIf(fn () => (string) $this->input('processes_rubber_on_farm') === '1'),
                Rule::in(array_keys(Planter::processPlanOptions())),
            ],
            'group_name' => ['nullable', 'string', 'max:160'],
            'group_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'applicant name',
            'rdd_division' => 'RDD officer division',
            'farm_name' => 'farm name',
            'address' => 'farm address',
            'business_type' => 'nature of business',
            'already_certified' => 'existing certification',
            'certification_standard' => 'certification standard',
            'prior_certificate_document' => 'certificate photocopy',
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
