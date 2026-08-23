<?php

namespace App\Http\Requests\Planters;

use App\Http\Requests\Concerns\ProtectsRegistrationFromSpam;
use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'district' => $this->filled('district') ? $this->district : null,
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
            'district' => ['nullable', Rule::in(Planter::districts())],
            'address' => ['nullable', 'string', 'max:500'],
            'application_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
