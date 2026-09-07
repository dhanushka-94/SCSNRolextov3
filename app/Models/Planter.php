<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Planter extends Authenticatable
{
    use Notifiable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const TYPE_ONLINE = 'online';

    public const TYPE_OFFLINE = 'offline';

    public const AUDIT_NOT_STARTED = 'not_started';

    public const AUDIT_OPEN = 'audit_open';

    public const AUDIT_IN_PROGRESS = 'auditing_in_progress';

    public const AUDIT_IN_REVIEW = 'audit_in_review';

    public const AUDIT_RESULT = 'audit_result';

    public const AUDIT_OUTCOME_CERTIFIED = 'certified';

    public const AUDIT_OUTCOME_CONDITIONAL = 'conditional';

    public const AUDIT_OUTCOME_NOT_CERTIFIED = 'not_certified';

    public const BUSINESS_INDIVIDUAL = 'individual';

    public const BUSINESS_PARTNERSHIP = 'partnership';

    public const BUSINESS_COMPANY = 'company';

    public const BUSINESS_LIMITED = 'limited';

    public const BUSINESS_SOCIETY = 'society';

    public const BUSINESS_OTHER = 'other';

    public const PROCESS_PLAN_YES = 'yes';

    public const PROCESS_PLAN_NO = 'no';

    public const PROCESS_PLAN_NA = 'na';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'temporary_id',
        'identification_number',
        'name',
        'nic',
        'email',
        'phone',
        'whatsapp',
        'fax',
        'district',
        'district_id',
        'rdd_division',
        'rdo_division_id',
        'farm_name',
        'address',
        'latitude',
        'longitude',
        'business_type',
        'already_certified',
        'certification_standard',
        'prior_certificate_document',
        'certification_rejected_or_suspended',
        'certification_issue_reason',
        'crops_products',
        'aware_of_certification',
        'has_certification_leaflet',
        'processes_rubber_on_farm',
        'has_process_plan',
        'group_name',
        'group_address',
        'registration_type',
        'application_document',
        'password',
        'status',
        'audit_status',
        'audit_result_outcome',
        'audit_result_notes',
        'audit_status_updated_at',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'last_login_at' => 'datetime',
            'audit_status_updated_at' => 'datetime',
            'already_certified' => 'boolean',
            'certification_rejected_or_suspended' => 'boolean',
            'aware_of_certification' => 'boolean',
            'has_certification_leaflet' => 'boolean',
            'processes_rubber_on_farm' => 'boolean',
            'crops_products' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function hasMapPin(): bool
    {
        return filled($this->latitude) && filled($this->longitude);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(PlanterAudit::class);
    }

    public function firstAudit(): HasOne
    {
        return $this->hasOne(PlanterAudit::class)
            ->where('round', PlanterAudit::ROUND_FIRST)
            ->where('is_current', true);
    }

    public function finalAudit(): HasOne
    {
        return $this->hasOne(PlanterAudit::class)
            ->where('round', PlanterAudit::ROUND_FINAL)
            ->where('is_current', true);
    }

    public function firstAuditAttempts(): HasMany
    {
        return $this->hasMany(PlanterAudit::class)
            ->where('round', PlanterAudit::ROUND_FIRST)
            ->orderByDesc('attempt_number');
    }

    public function finalAuditAttempts(): HasMany
    {
        return $this->hasMany(PlanterAudit::class)
            ->where('round', PlanterAudit::ROUND_FINAL)
            ->orderByDesc('attempt_number');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function currentCertificate(): HasOne
    {
        return $this->hasOne(Certificate::class)
            ->where('is_current', true)
            ->where('status', Certificate::STATUS_ISSUED);
    }

    public function latestCertificate(): HasOne
    {
        return $this->hasOne(Certificate::class)->latestOfMany();
    }

    public function districtRecord(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function rdoDivision(): BelongsTo
    {
        return $this->belongsTo(RdoDivision::class, 'rdo_division_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isOffline(): bool
    {
        return $this->registration_type === self::TYPE_OFFLINE;
    }

    public function hasPassword(): bool
    {
        return filled($this->getRawOriginal('password') ?? $this->attributes['password'] ?? null);
    }

    public function hasApplicationDocument(): bool
    {
        return filled($this->application_document);
    }

    public function hasPriorCertificateDocument(): bool
    {
        return filled($this->prior_certificate_document);
    }

    public static function registrationTypes(): array
    {
        return [
            self::TYPE_ONLINE => 'Online',
            self::TYPE_OFFLINE => 'Offline',
        ];
    }

    /**
     * @return array<string, array{si: string, en: string}>
     */
    public static function businessTypes(): array
    {
        return [
            self::BUSINESS_INDIVIDUAL => ['si' => 'තනි පුද්ගල', 'en' => 'Individual'],
            self::BUSINESS_PARTNERSHIP => ['si' => 'හවුල් ව්‍යාපාර', 'en' => 'Partnership'],
            self::BUSINESS_COMPANY => ['si' => 'සමාගම්', 'en' => 'Company'],
            self::BUSINESS_LIMITED => ['si' => 'සීමාකාරී', 'en' => 'Limited'],
            self::BUSINESS_SOCIETY => ['si' => 'සමිති', 'en' => 'Society'],
            self::BUSINESS_OTHER => ['si' => 'අනෙකුත්', 'en' => 'Other'],
        ];
    }

    /**
     * @return array<string, array{si: string, en: string}>
     */
    public static function processPlanOptions(): array
    {
        return [
            self::PROCESS_PLAN_YES => ['si' => 'ඔව්', 'en' => 'Yes'],
            self::PROCESS_PLAN_NO => ['si' => 'නැත', 'en' => 'No'],
            self::PROCESS_PLAN_NA => ['si' => 'අදාළ නැත', 'en' => 'Not applicable'],
        ];
    }

    public function businessTypeLabel(): string
    {
        return self::businessTypes()[$this->business_type]['en'] ?? '—';
    }

    public function yesNoLabel(?bool $value): string
    {
        if ($value === null) {
            return '—';
        }

        return $value ? 'Yes' : 'No';
    }

    public function processPlanLabel(): string
    {
        return self::processPlanOptions()[$this->has_process_plan]['en'] ?? '—';
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public static function districts(): array
    {
        return District::activeNames();
    }

    /**
     * Two-letter district codes used in SCSNR identification numbers.
     *
     * @return array<string, string>
     */
    public static function districtCodes(): array
    {
        return District::query()
            ->orderBy('name')
            ->pluck('code', 'name')
            ->all();
    }

    public static function districtCode(?string $district): string
    {
        return District::codeFor($district);
    }

    public function qrDownloadName(string $extension): string
    {
        $id = str_replace('/', '-', $this->identification_number ?: $this->temporary_id);

        return $id.'.'.$extension;
    }

    public static function generateTemporaryId(): string
    {
        return DB::transaction(function () {
            $prefix = 'TMP-'.now()->year.'-';

            $latest = static::query()
                ->where('temporary_id', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('temporary_id')
                ->value('temporary_id');

            $sequence = $latest ? ((int) substr($latest, -6)) + 1 : 1;

            return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * @return list<string>
     */
    public static function auditStageKeys(): array
    {
        return [
            'registration_approved',
            'first_audit',
            'final_audit',
            'audit_result',
        ];
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function auditStages(): array
    {
        return [
            'registration_approved' => [
                'label' => 'Registration approved',
                'description' => 'Planter registration verified and SCSNR identification issued.',
            ],
            'first_audit' => [
                'label' => 'First Audit',
                'description' => 'First auditor team completes the SCSNR module checklist.',
            ],
            'final_audit' => [
                'label' => 'Final Audit',
                'description' => 'Final auditor team reviews evidence and completes the final checklist.',
            ],
            'audit_result' => [
                'label' => 'Audit result',
                'description' => 'Final certification decision published to the planter record.',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function auditResultOutcomes(): array
    {
        return [
            self::AUDIT_OUTCOME_CERTIFIED => 'Certified',
            self::AUDIT_OUTCOME_CONDITIONAL => 'Conditional certification',
            self::AUDIT_OUTCOME_NOT_CERTIFIED => 'Not certified',
        ];
    }

    public function currentAuditStageKey(): string
    {
        if (! $this->isApproved()) {
            return 'registration_approved';
        }

        $first = $this->relationLoaded('firstAudit') ? $this->firstAudit : $this->firstAudit()->first();
        $final = $this->relationLoaded('finalAudit') ? $this->finalAudit : $this->finalAudit()->first();

        if ($final?->isComplete() || ($this->audit_status === self::AUDIT_RESULT && filled($this->audit_result_outcome))) {
            return 'audit_result';
        }

        if ($final) {
            return 'final_audit';
        }

        if ($first) {
            return 'first_audit';
        }

        return 'first_audit';
    }

    public function auditStageState(string $stageKey): string
    {
        if (! $this->isApproved()) {
            return $stageKey === 'registration_approved' ? 'current' : 'upcoming';
        }

        if ($stageKey === 'registration_approved') {
            return 'completed';
        }

        $first = $this->relationLoaded('firstAudit') ? $this->firstAudit : $this->firstAudit()->first();
        $final = $this->relationLoaded('finalAudit') ? $this->finalAudit : $this->finalAudit()->first();

        return match ($stageKey) {
            'first_audit' => match (true) {
                $first === null => 'current',
                $first->isComplete() => 'completed',
                default => 'current',
            },
            'final_audit' => match (true) {
                $first === null || ! $first->canProceedToFinal() => 'upcoming',
                $final === null => ($first->canProceedToFinal() ? 'current' : 'upcoming'),
                $final->isComplete() => 'completed',
                default => 'current',
            },
            'audit_result' => match (true) {
                $final?->isComplete() => 'completed',
                $first?->status === PlanterAudit::STATUS_FAILED => 'completed',
                default => 'upcoming',
            },
            default => 'upcoming',
        };
    }

    /**
     * @return list<array{key: string, label: string, description: string, state: string, children: list<mixed>}>
     */
    public function auditTimelineTree(): array
    {
        $this->loadMissing(['firstAudit', 'finalAudit']);

        $stages = self::auditStages();
        $tree = [];

        foreach (self::auditStageKeys() as $key) {
            $description = $stages[$key]['description'];

            if ($key === 'first_audit' && $this->firstAudit) {
                $description .= ' Attempt #'.$this->firstAudit->attempt_number.'. Status: '.$this->firstAudit->statusLabel().'.';
            }

            if ($key === 'final_audit' && $this->finalAudit) {
                $description .= ' Attempt #'.$this->finalAudit->attempt_number.'. Status: '.$this->finalAudit->statusLabel().'.';
            }

            if ($key === 'audit_result' && filled($this->audit_result_outcome)) {
                $description .= ' Result: '.(self::auditResultOutcomes()[$this->audit_result_outcome] ?? $this->audit_result_outcome).'.';
            }

            $tree[] = [
                'key' => $key,
                'label' => $stages[$key]['label'],
                'description' => $description,
                'state' => $this->auditStageState($key),
                'children' => [],
            ];
        }

        return $tree;
    }

    public function canSendToFirstAudit(): bool
    {
        return $this->isApproved()
            && ! $this->audits()->where('round', PlanterAudit::ROUND_FIRST)->exists();
    }

    public function canSendToFinalAudit(): bool
    {
        if (! $this->isApproved() || $this->audits()->where('round', PlanterAudit::ROUND_FINAL)->exists()) {
            return false;
        }

        $first = $this->relationLoaded('firstAudit') ? $this->firstAudit : $this->firstAudit()->first();

        return $first?->canProceedToFinal() === true;
    }

    public function isAuditComplete(): bool
    {
        return $this->isApproved()
            && ($this->audit_status ?? self::AUDIT_NOT_STARTED) === self::AUDIT_RESULT
            && filled($this->audit_result_outcome);
    }

    public function isAuditActive(): bool
    {
        return $this->isApproved() && ! $this->isAuditComplete();
    }

    public function canIssueCertificate(): bool
    {
        return app(\App\Services\CertificateService::class)->canIssue($this);
    }

    public function hasActiveCertificate(): bool
    {
        return $this->currentCertificate()->exists();
    }
}
