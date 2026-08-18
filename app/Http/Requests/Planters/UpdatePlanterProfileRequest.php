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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Planter $planter */
        $planter = $this->user('planter');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'string', 'email', 'max:190', Rule::unique('planters', 'email')->ignore($planter->id)],
            'phone' => ['required', 'string', 'max:30'],
            'district' => ['required', Rule::in(Planter::districts())],
            'address' => ['required', 'string', 'max:500'],
        ];
    }
}
