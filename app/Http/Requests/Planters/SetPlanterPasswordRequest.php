<?php

namespace App\Http\Requests\Planters;

use App\Models\Planter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SetPlanterPasswordRequest extends FormRequest
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
            'temporary_id' => ['required', 'string'],
            'nic' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function planter(): Planter
    {
        $planter = Planter::query()
            ->where('temporary_id', trim($this->string('temporary_id')->toString()))
            ->where('nic', trim($this->string('nic')->toString()))
            ->first();

        if (! $planter) {
            throw ValidationException::withMessages([
                'temporary_id' => 'No approved registration matches this temporary ID and NIC.',
            ]);
        }

        if ($planter->isPending()) {
            throw ValidationException::withMessages([
                'temporary_id' => 'Your registration is still waiting for approval.',
            ]);
        }

        if ($planter->isRejected()) {
            throw ValidationException::withMessages([
                'temporary_id' => 'This registration was rejected. Please contact SCSNR administration.',
            ]);
        }

        if ($planter->hasPassword()) {
            throw ValidationException::withMessages([
                'temporary_id' => 'A password is already set for this account. Please sign in.',
            ]);
        }

        return $planter;
    }
}
