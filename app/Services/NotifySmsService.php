<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use Illuminate\Support\Facades\Http;
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

    /** Languages every template is written in, in display order. */
    public const LANGUAGES = ['en', 'si', 'ta'];

    /**
     * @param  array<string, string|int|null>  $replacements
     */
    public function render(string $key, array $replacements = [], string $lang = 'en'): ?string
    {
        $template = $this->template($key);

        if ($template === null) {
            return null;
        }

        $body = (string) ($this->localised($template, 'body', $lang) ?? '');
        $defaults = [
            'brand' => (string) config('app.short_name', 'SPPNR'),
        ];

        foreach (array_merge($defaults, $replacements) as $name => $value) {
            $body = str_replace('{'.$name.'}', (string) $value, $body);
        }

        return $body;
    }

    public function renderSample(string $key, string $lang = 'en'): ?string
    {
        $template = $this->template($key);

        if ($template === null) {
            return null;
        }

        /** @var array<string, string|int|null> $sample */
        $sample = array_merge(
            $template['sample'] ?? [],
            $lang === 'en' ? [] : ($template['sample_'.$lang] ?? []),
        );

        return $this->render($key, $sample, $lang);
    }

    /**
     * The template text in one language, or null when that language is missing.
     * English is never missing for a template that exists.
     *
     * @param  array<string, mixed>  $template
     */
    public function localised(array $template, string $field, string $lang): ?string
    {
        $suffix = $lang === 'en' ? '' : '_'.$lang;
        $value = $template[$field.$suffix] ?? null;

        if (blank($value) && $lang !== 'en') {
            $value = $template[$field] ?? null;
        }

        return blank($value) ? null : (string) $value;
    }

    /** The template name in one language (falls back to English). */
    public function templateName(string $key, string $lang = 'en'): string
    {
        $template = $this->template($key) ?? [];

        return $this->localised($template, 'name', $lang) ?? $key;
    }

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
            Log::warning('Notify SMS skipped: empty or over 320 characters.', [
                'length' => mb_strlen($message),
            ]);

            return false;
        }

        if ($this->needsUnicode($message)) {
            return $this->sendUnicode($normalized, $message);
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

    /**
     * Sinhala and Tamil text only reaches the handset when the gateway is told
     * the message is Unicode (type=unicode). Without it, the gateway turns every
     * non-Latin character into "?". The bundled SmsApi client cannot send that
     * flag, so Unicode messages are posted to the same endpoint directly.
     */
    public function needsUnicode(string $message): bool
    {
        return preg_match('/[^\x00-\x7F]/', $message) === 1;
    }

    private function sendUnicode(string $normalizedPhone, string $message): bool
    {
        // Notify.lk limits Unicode messages to 268 characters in total.
        if (mb_strlen($message) > 268) {
            Log::warning('Notify Unicode SMS skipped: over 268 characters.', [
                'length' => mb_strlen($message),
            ]);

            return false;
        }

        $config = config('services.notify');

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post(rtrim((string) $config['base_url'], '/').'/send', [
                    'user_id' => (string) $config['user_id'],
                    'api_key' => (string) $config['api_key'],
                    'sender_id' => (string) $config['sender_id'],
                    'to' => $normalizedPhone,
                    'message' => $message,
                    'type' => 'unicode',
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return true;
            }

            Log::error('Notify Unicode SMS rejected.', [
                'phone' => $normalizedPhone,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Notify Unicode SMS failed.', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, string|int|null>  $replacements
     */
    public function sendTemplate(string $key, string $to, array $replacements = []): bool
    {
        $message = $this->render($key, $replacements);

        if ($message === null) {
            Log::warning('Notify SMS skipped: unknown template.', ['key' => $key]);

            return false;
        }

        if (mb_strlen($message) > 320) {
            $message = mb_substr($message, 0, 317).'...';
        }

        return $this->send($to, $message);
    }

    public function sendSample(string $key, string $to, string $lang = 'en'): bool
    {
        $message = $this->renderSample($key, $lang);

        if ($message === null) {
            return false;
        }

        $label = $this->templateName($key, $lang);
        $prefixed = '[SAMPLE '.$label.'] '.$message;

        if (mb_strlen($prefixed) > 320) {
            $prefixed = $message;
        }

        return $this->send($to, $prefixed);
    }

    /**
     * @param  list<string>|null  $languages  defaults to English only
     * @return array{sent: list<string>, failed: list<string>}
     */
    public function sendAllSamples(string $to, ?array $languages = null): array
    {
        $sent = [];
        $failed = [];

        foreach ($languages ?? ['en'] as $lang) {
            foreach (array_keys($this->templates()) as $key) {
                // English keeps the plain template key, so existing API clients see the same values.
                $id = $lang === 'en' ? $key : $lang.':'.$key;

                if ($this->sendSample($key, $to, $lang)) {
                    $sent[] = $id;
                } else {
                    $failed[] = $id;
                }
            }
        }

        return compact('sent', 'failed');
    }

    public function notifyPlanter(Planter $planter, string $templateKey, array $replacements = []): bool
    {
        return $this->sendTemplate($templateKey, (string) $planter->phone, array_merge(
            $this->planterIdentity($planter),
            $replacements,
        ));
    }

    /**
     * Name and SPPNR ID included on every planter SMS.
     * Before approval the SPPNR ID line reads "Pending".
     *
     * @return array{name: string, registration_number: string}
     */
    private function planterIdentity(Planter $planter): array
    {
        return [
            'name' => $planter->name ?: 'N/A',
            'registration_number' => $planter->identification_number ?: 'Pending',
        ];
    }

    public function sendRegistrationSubmitted(Planter $planter): bool
    {
        return $this->notifyPlanter($planter, 'registration_submitted', [
            'reference' => $planter->temporary_id ?: 'pending',
        ]);
    }

    public function sendRegistrationApproved(Planter $planter): bool
    {
        return $this->notifyPlanter($planter, 'registration_approved', [
            'name' => $planter->name ?: 'N/A',
            'registration_number' => $planter->identification_number ?: 'N/A',
        ]);
    }

    public function sendRegistrationRejected(Planter $planter): bool
    {
        $reason = trim((string) ($planter->rejection_reason ?: 'See RRISL'));

        if (mb_strlen($reason) > 80) {
            $reason = mb_substr($reason, 0, 77).'...';
        }

        return $this->notifyPlanter($planter, 'registration_rejected', [
            'registration_number' => $planter->identification_number ?: ($planter->temporary_id ?: 'N/A'),
            'reason' => $reason,
        ]);
    }

    public function sendAuditQueued(Planter $planter, string $round): bool
    {
        $key = $round === PlanterAudit::ROUND_FINAL
            ? 'second_audit_queued'
            : 'first_audit_queued';

        return $this->notifyPlanter($planter, $key, [
            'registration_number' => $planter->identification_number ?: 'N/A',
        ]);
    }

    public function sendAuditResult(Planter $planter, PlanterAudit $audit): bool
    {
        $key = $audit->round === PlanterAudit::ROUND_FINAL
            ? 'second_audit_result'
            : 'first_audit_result';

        $outcome = strtoupper(str_replace('_', ' ', (string) $audit->status));
        $notes = trim((string) ($audit->outcome_notes ?: ''));

        if ($notes === '') {
            $notes = $audit->round === PlanterAudit::ROUND_FINAL
                ? ($audit->status === PlanterAudit::STATUS_FAILED ? 'Contact RRISL for next steps.' : 'Certificate may follow.')
                : ($audit->status === PlanterAudit::STATUS_FAILED ? 'Contact RRISL for next steps.' : 'Second Audit may follow.');
        }

        if (mb_strlen($notes) > 60) {
            $notes = mb_substr($notes, 0, 57).'...';
        }

        return $this->notifyPlanter($planter, $key, [
            'registration_number' => $planter->identification_number ?: 'N/A',
            'outcome' => $outcome,
            'notes' => $notes,
        ]);
    }

    public function sendCertificateIssued(Planter $planter, Certificate $certificate): bool
    {
        return $this->notifyPlanter($planter, 'certificate_issued', [
            'certificate_number' => $certificate->certificate_number,
            'verify_url' => $certificate->verifyUrl(),
        ]);
    }

    public function sendCertificateRevoked(Planter $planter, Certificate $certificate): bool
    {
        $reason = trim((string) ($certificate->revoke_reason ?: 'See RRISL'));

        if (mb_strlen($reason) > 80) {
            $reason = mb_substr($reason, 0, 77).'...';
        }

        return $this->notifyPlanter($planter, 'certificate_revoked', [
            'certificate_number' => $certificate->certificate_number,
            'reason' => $reason,
        ]);
    }

    /**
     * Step SMS actions available to resend for this planter's current history.
     *
     * @return list<array{key: string, label: string}>
     */
    public function availableResendActions(Planter $planter): array
    {
        $planter->loadMissing(['firstAudit', 'finalAudit', 'currentCertificate', 'latestCertificate']);

        $actions = [
            [
                'key' => 'registration_submitted',
                'label' => 'Registration submitted',
            ],
        ];

        if ($planter->isApproved()) {
            $actions[] = [
                'key' => 'registration_approved',
                'label' => 'Registration approved',
            ];
        }

        if ($planter->isRejected()) {
            $actions[] = [
                'key' => 'registration_rejected',
                'label' => 'Registration rejected',
            ];
        }

        if ($planter->firstAudit) {
            $actions[] = [
                'key' => 'first_audit_queued',
                'label' => 'First Audit queued',
            ];

            if ($planter->firstAudit->isComplete()) {
                $actions[] = [
                    'key' => 'first_audit_result',
                    'label' => 'First Audit result',
                ];
            }
        }

        if ($planter->finalAudit) {
            $actions[] = [
                'key' => 'second_audit_queued',
                'label' => 'Second Audit queued',
            ];

            if ($planter->finalAudit->isComplete()) {
                $actions[] = [
                    'key' => 'second_audit_result',
                    'label' => 'Second Audit result',
                ];
            }
        }

        $certificate = $planter->latestCertificate ?? $planter->currentCertificate;

        if ($certificate?->isIssued() || $certificate?->isRevoked()) {
            $actions[] = [
                'key' => 'certificate_issued',
                'label' => 'Certificate issued',
            ];
        }

        if ($certificate?->isRevoked()) {
            $actions[] = [
                'key' => 'certificate_revoked',
                'label' => 'Certificate revoked',
            ];
        }

        return $actions;
    }

    public function resendStep(Planter $planter, string $key): bool
    {
        $allowed = collect($this->availableResendActions($planter))->pluck('key')->all();

        if (! in_array($key, $allowed, true)) {
            return false;
        }

        $planter->loadMissing(['firstAudit', 'finalAudit', 'currentCertificate', 'latestCertificate']);

        return match ($key) {
            'registration_submitted' => $this->sendRegistrationSubmitted($planter),
            'registration_approved' => $this->sendRegistrationApproved($planter),
            'registration_rejected' => $this->sendRegistrationRejected($planter),
            'first_audit_queued' => $this->sendAuditQueued($planter, PlanterAudit::ROUND_FIRST),
            'first_audit_result' => $planter->firstAudit
                ? $this->sendAuditResult($planter, $planter->firstAudit)
                : false,
            'second_audit_queued' => $this->sendAuditQueued($planter, PlanterAudit::ROUND_FINAL),
            'second_audit_result' => $planter->finalAudit
                ? $this->sendAuditResult($planter, $planter->finalAudit)
                : false,
            'certificate_issued' => ($cert = $planter->latestCertificate ?? $planter->currentCertificate)
                ? $this->sendCertificateIssued($planter, $cert)
                : false,
            'certificate_revoked' => ($cert = $planter->latestCertificate ?? $planter->currentCertificate)
                ? $this->sendCertificateRevoked($planter, $cert)
                : false,
            default => false,
        };
    }
}
