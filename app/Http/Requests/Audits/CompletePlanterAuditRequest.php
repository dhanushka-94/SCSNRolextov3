<?php

namespace App\Http\Requests\Audits;

use App\Models\PlanterAudit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompletePlanterAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        $audit = $this->route('audit');

        return $this->user('web')?->canWorkAuditRound($audit->round) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'outcome_notes' => $this->filled('outcome_notes')
                ? $this->string('outcome_notes')->trim()->toString()
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                PlanterAudit::STATUS_PASSED,
                PlanterAudit::STATUS_CONDITIONAL,
                PlanterAudit::STATUS_FAILED,
            ])],
            'outcome_notes' => ['nullable', 'string', 'max:2000'],
            'signature' => [
                'required',
                'string',
                'max:600000',
                'regex:/^data:image\/png;base64,[A-Za-z0-9+\/]+={0,2}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $binary = base64_decode(substr((string) $value, strlen('data:image/png;base64,')), true);

                    if ($binary === false || @getimagesizefromstring($binary) === false) {
                        $fail('The digital signature is not a valid image. Please sign again.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'audit outcome',
            'outcome_notes' => 'outcome notes',
            'signature' => 'digital signature',
        ];
    }
}
