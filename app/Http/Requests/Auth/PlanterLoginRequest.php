<?php

namespace App\Http\Requests\Auth;

use App\Models\Planter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlanterLoginRequest extends FormRequest
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
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim($this->string('login')->toString());
        $field = $this->loginField($login);

        $planter = Planter::query()->where($field, $login)->first();

        if (! $planter) {
            $this->failAttempt();
        }

        if ($planter->isPending()) {
            throw ValidationException::withMessages([
                'login' => 'Your registration is waiting for approval. After approval you can create a password and sign in.',
            ]);
        }

        if ($planter->isRejected()) {
            throw ValidationException::withMessages([
                'login' => 'This registration was rejected. Please contact SCSNR administration.',
            ]);
        }

        if (! $planter->hasPassword()) {
            throw ValidationException::withMessages([
                'login' => 'Your registration is approved and active. Create a password before signing in.',
            ]);
        }

        if (! Auth::guard('planter')->attempt([
            $field => $login,
            'password' => $this->input('password'),
        ], $this->boolean('remember'))) {
            $this->failAttempt();
        }

        /** @var Planter $authenticated */
        $authenticated = Auth::guard('planter')->user();
        $authenticated->forceFill(['last_login_at' => now()])->save();

        RateLimiter::clear($this->throttleKey());
    }

    protected function loginField(string $login): string
    {
        if (str_contains($login, '/')) {
            return 'identification_number';
        }

        return filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'identification_number';
    }

    public function failAttempt(): never
    {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'These credentials do not match our records.',
        ]);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate('planter|'.Str::lower($this->string('login')).'|'.$this->ip());
    }
}
