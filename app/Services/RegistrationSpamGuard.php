<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RegistrationSpamGuard
{
    public function assertNotSpam(Request $request): void
    {
        $this->rejectHoneypot($request);
        $this->ensureFormTiming($request);
        $this->verifyRecaptcha($request);
    }

    public function markFormStarted(): void
    {
        session(['planter_registration_started_at' => now()->timestamp]);
    }

    public function captchaEnabled(): bool
    {
        return filled(config('registration.recaptcha.site_key'))
            && filled(config('registration.recaptcha.secret_key'));
    }

    protected function rejectHoneypot(Request $request): void
    {
        $field = config('registration.honeypot_field');

        if (blank($field) || blank($request->input($field))) {
            return;
        }

        throw ValidationException::withMessages([
            'registration' => 'Unable to submit your registration. Please try again.',
        ]);
    }

    protected function ensureFormTiming(Request $request): void
    {
        $startedAt = session('planter_registration_started_at');

        if (! is_int($startedAt) && ! is_numeric($startedAt)) {
            throw ValidationException::withMessages([
                'registration' => 'Your session expired. Please reload the registration page and try again.',
            ]);
        }

        $elapsed = now()->timestamp - (int) $startedAt;
        $minSeconds = config('registration.min_seconds');
        $maxSeconds = config('registration.max_seconds');

        if ($elapsed < $minSeconds) {
            throw ValidationException::withMessages([
                'registration' => 'Please review your application carefully before submitting.',
            ]);
        }

        if ($elapsed > $maxSeconds) {
            throw ValidationException::withMessages([
                'registration' => 'Your session expired. Please reload the registration page and try again.',
            ]);
        }
    }

    protected function verifyRecaptcha(Request $request): void
    {
        if (! $this->captchaEnabled()) {
            return;
        }

        $token = $request->string('g-recaptcha-response')->trim()->toString();

        if ($token === '') {
            throw ValidationException::withMessages([
                'registration' => 'Please complete the reCAPTCHA check before submitting.',
            ]);
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('registration.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! $response->ok() || ! $response->json('success')) {
            throw ValidationException::withMessages([
                'registration' => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }
    }
}
