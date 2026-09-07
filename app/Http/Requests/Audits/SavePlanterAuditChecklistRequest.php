<?php

namespace App\Http\Requests\Audits;

use App\Models\PlanterAuditChecklistAnswer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanterAuditChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        $audit = $this->route('audit');

        return $this->user('web')?->canWorkAuditRound($audit->round) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.result' => ['required', Rule::in(array_keys(PlanterAuditChecklistAnswer::results()))],
            'answers.*.comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
