<?php

namespace App\Services;

use App\Models\Planter;
use Illuminate\Support\Facades\Log;
use NotifyLk\Api\SmsApi;
use Throwable;

class NotifySmsService
{
    public function enabled(): bool
    {
        $config = config('services.notify');

        return ($config['enabled'] ?? false)
            && filled($config['user_id'] ?? null)
            && filled($config['api_key'] ?? null)
            && filled($config['sender_id'] ?? null);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function templates(): array
    {
        return config('sms.templates', []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function template(string $key): ?array
    {
        return $this->templates()[$key] ?? null;
    }

    /**
     * @param  array<string, string|int|null>  $replacements
     */
    public function render(string $key, array $replacements = []): ?string
    {
        $template = $this->template($key);

        if ($template === null) {
            return null;
        }

        $body = (string) ($template['body'] ?? '');
        $defaults = [
            'brand' => (string) config('app.short_name', 'SPPNR'),
        ];

        foreach (array_merge($defaults, $replacements) as $name => $value) {
            $body = str_replace('{'.$name.'}', (string) $value, $body);
        }

        return $body;
    }

    public function renderSample(string $key): ?string
    {
        $template = $this->template($key);

        if ($template === null) {
            return null;
        }

        /** @var array<string, string|int|null> $sample */
        $sample = $template['sample'] ?? [];

        return $this->render($key, $sample);
    }

    /**
     * Normalize Sri Lankan mobiles to 94XXXXXXXXX (Notify.lk preferred format).
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '94') && strlen($digits) === 11) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '94'.substr($digits, 1);
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            return '94'.$digits;
        }

        return null;
    }

    public function send(string $to, string $message): bool
    {
        if (! $this->enabled()) {
            Log::debug('Notify SMS skipped: gateway disabled or credentials missing.');

            return false;
        }

        $normalized = $this->normalizePhone($to);

        if ($normalized === null) {
            Log::warning('Notify SMS skipped: invalid phone number.', ['phone' => $to]);

            return false;
        }

        $message = trim($message);

        if ($message === '' || mb_strlen($message) > 320) {
            Log::warning('Notify SMS skipped: empty or over 320 characters.');

            return false;
        }

        try {
            $api = new SmsApi;
            $config = config('services.notify');

            $api->sendSMS(
                (string) $config['user_id'],
                (string) $config['api_key'],
                $message,
                $normalized,
                (string) $config['sender_id'],
            );

            return true;
        } catch (Throwable $e) {
            Log::error('Notify SMS failed.', [
                'phone' => $normalized,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendTemplate(string $key, string $to, array $replacements = []): bool
    {
        $message = $this->render($key, $replacements);

        if ($message === null) {
            Log::warning('Notify SMS skipped: unknown template.', ['key' => $key]);

            return false;
        }

        return $this->send($to, $message);
    }

    public function sendSample(string $key, string $to): bool
    {
        $message = $this->renderSample($key);

        if ($message === null) {
            return false;
        }

        $label = $this->template($key)['name'] ?? $key;
        $prefixed = '[SAMPLE '.$label.'] '.$message;

        if (mb_strlen($prefixed) > 320) {
            $prefixed = $message;
        }

        return $this->send($to, $prefixed);
    }

    /**
     * @return array{sent: list<string>, failed: list<string>}
     */
    public function sendAllSamples(string $to): array
    {
        $sent = [];
        $failed = [];

        foreach (array_keys($this->templates()) as $key) {
            if ($this->sendSample($key, $to)) {
                $sent[] = $key;
            } else {
                $failed[] = $key;
            }
        }

        return compact('sent', 'failed');
    }

    public function sendRegistrationSubmitted(Planter $planter): bool
    {
        return $this->sendTemplate('registration_submitted', (string) $planter->phone, [
            'reference' => $planter->temporary_id ?: 'pending',
        ]);
    }
}
