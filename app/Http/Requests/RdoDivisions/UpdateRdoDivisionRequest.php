<?php

namespace App\Http\Requests\RdoDivisions;

use App\Models\RdoDivision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdoDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web')?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'code' => strtoupper(trim((string) $this->input('code'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var RdoDivision $division */
        $division = $this->route('rdo_division');

        return [
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('rdo_divisions', 'name')
                    ->where(fn ($query) => $query->where('district_id', $this->integer('district_id')))
                    ->ignore($division->id),
            ],
            'code' => [
                'required',
                'string',
                'size:4',
                'regex:/^[A-Z0-9]{4}$/',
                Rule::unique('rdo_divisions', 'code')->ignore($division->id),
            ],
            'status' => ['required', Rule::in(array_keys(RdoDivision::statuses()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'district_id' => 'district',
            'name' => 'division name',
            'code' => 'division code',
        ];
    }
}
