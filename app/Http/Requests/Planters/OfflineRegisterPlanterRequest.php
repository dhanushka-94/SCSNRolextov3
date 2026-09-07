<?php

namespace App\Http\Requests\Planters;

use App\Http\Requests\Concerns\ProtectsRegistrationFromSpam;
use App\Support\PlanterLocation;
use Illuminate\Foundation\Http\FormRequest;

class OfflineRegisterPlanterRequest extends FormRequest
{
    use ProtectsRegistrationFromSpam;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareSpamProtection();

        $this->merge([
            'email' => $this->filled('email') ? $this->email : null,
            'address' => $this->filled('address') ? $this->address : null,
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
            'email' => ['nullable', 'string', 'email', 'max:190', 'unique:planters,email'],
            'phone' => ['required', 'string', 'max:30'],
            'district_id' => PlanterLocation::districtIdRules(),
            'rdo_division_id' => PlanterLocation::rdoDivisionIdRules(),
            'address' => ['nullable', 'string', 'max:500'],
            'application_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'district_id' => 'district',
            'rdo_division_id' => 'Rubber Development Officer division',
        ];
    }
}
