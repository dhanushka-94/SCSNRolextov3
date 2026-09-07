<?php

namespace App\Http\Requests\Districts;

use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web')?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'unique:districts,name'],
            'code' => ['required', 'string', 'size:4', 'regex:/^[A-Z0-9]{4}$/', 'unique:districts,code'],
            'status' => ['required', Rule::in(array_keys(District::statuses()))],
        ];
    }
}
