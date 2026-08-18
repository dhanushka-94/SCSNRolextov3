<?php

namespace App\Http\Requests\Planters;

use Illuminate\Foundation\Http\FormRequest;

class RejectPlanterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
