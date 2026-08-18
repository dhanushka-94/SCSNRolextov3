<?php

namespace App\Http\Requests\Planters;

use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPlanterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'nic' => ['required', 'string', 'max:20', 'unique:planters,nic'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:planters,email'],
            'phone' => ['required', 'string', 'max:30'],
            'district' => ['required', Rule::in(Planter::districts())],
            'address' => ['required', 'string', 'max:500'],
        ];
    }
}
