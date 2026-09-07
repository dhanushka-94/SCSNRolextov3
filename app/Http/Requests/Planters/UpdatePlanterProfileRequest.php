<?php

namespace App\Http\Requests\Planters;

use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanterProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('planter')?->isApproved() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(\App\Support\FarmCoordinates::prepare(
            $this->input('latitude'),
            $this->input('longitude'),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Planter $planter */
        $planter = $this->user('planter');

        return array_merge([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'string', 'email', 'max:190', Rule::unique('planters', 'email')->ignore($planter->id)],
            'phone' => ['required', 'string', 'max:30'],
            'district_id' => \App\Support\PlanterLocation::districtIdRules(),
            'rdo_division_id' => \App\Support\PlanterLocation::rdoDivisionIdRules(),
            'address' => ['required', 'string', 'max:500'],
        ], \App\Support\FarmCoordinates::rules(required: true));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge([
            'district_id' => 'district',
            'rdo_division_id' => 'Rubber Development Officer division',
        ], \App\Support\FarmCoordinates::attributes());
    }
}
