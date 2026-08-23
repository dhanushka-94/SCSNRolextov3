<?php

namespace App\Http\Requests\Planters;

use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdatePlanterRequest extends FormRequest
{
    use ValidatesPlanterApplicationFields;

    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareApplicationFields();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Planter $planter */
        $planter = $this->route('planter');

        return array_merge([
            'name' => ['required', 'string', 'max:120'],
            'nic' => ['required', 'string', 'max:20', Rule::unique('planters', 'nic')->ignore($planter->id)],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('planters', 'email')->ignore($planter->id)],
            'phone' => ['required', 'string', 'max:30'],
            'district' => ['required', Rule::in(Planter::districts())],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Planter::statuses()))],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:500'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], $this->applicationFieldRules(requireCoreApplication: false));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->applicationFieldAttributes();
    }
}
