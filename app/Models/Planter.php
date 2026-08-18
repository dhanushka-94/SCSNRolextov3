<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'district',
        'address',
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
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public static function registrationTypes(): array
    {
        return [
            self::TYPE_ONLINE => 'Online',
            self::TYPE_OFFLINE => 'Offline',
        ];
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
        return array_keys(self::districtCodes());
    }

    /**
     * Two-letter district codes used in SCSNR identification numbers.
     *
     * @return array<string, string>
     */
    public static function districtCodes(): array
    {
        return [
            'Ampara' => 'Ap',
            'Anuradhapura' => 'An',
            'Badulla' => 'Bd',
            'Batticaloa' => 'Bt',
            'Colombo' => 'Co',
            'Galle' => 'Gl',
            'Gampaha' => 'Gp',
            'Hambantota' => 'Hb',
            'Jaffna' => 'Jf',
            'Kalutara' => 'Kt',
            'Kandy' => 'Kd',
            'Kegalle' => 'Kg',
            'Kilinochchi' => 'Kl',
            'Kurunegala' => 'Kr',
            'Mannar' => 'Mn',
            'Matale' => 'Mt',
            'Matara' => 'Mr',
            'Monaragala' => 'Mg',
            'Mullaitivu' => 'Mu',
            'Nuwara Eliya' => 'Ne',
            'Polonnaruwa' => 'Po',
            'Puttalam' => 'Pu',
            'Ratnapura' => 'Rt',
            'Trincomalee' => 'Tr',
            'Vavuniya' => 'Vv',
        ];
    }

    public static function districtCode(?string $district): string
    {
        return self::districtCodes()[$district] ?? 'Xx';
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
            self::AUDIT_OPEN,
            self::AUDIT_IN_PROGRESS,
            self::AUDIT_IN_REVIEW,
            self::AUDIT_RESULT,
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
            self::AUDIT_OPEN => [
                'label' => 'Audit open',
                'description' => 'Sustainability audit file opened and assigned for scheduling.',
            ],
            self::AUDIT_IN_PROGRESS => [
                'label' => 'Auditing in progress',
                'description' => 'Field inspection and plantation documentation review underway.',
            ],
            self::AUDIT_IN_REVIEW => [
                'label' => 'Audit in review',
                'description' => 'Audit findings submitted and under RRISL technical review.',
            ],
            self::AUDIT_RESULT => [
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

        return match ($this->audit_status ?? self::AUDIT_NOT_STARTED) {
            self::AUDIT_NOT_STARTED => self::AUDIT_OPEN,
            default => $this->audit_status,
        };
    }

    public function auditStageState(string $stageKey): string
    {
        if (! $this->isApproved()) {
            return $stageKey === 'registration_approved' ? 'current' : 'upcoming';
        }

        if ($stageKey === 'registration_approved') {
            return 'completed';
        }

        $stages = self::auditStageKeys();
        $currentIndex = array_search($this->currentAuditStageKey(), $stages, true);
        $stageIndex = array_search($stageKey, $stages, true);

        if ($stageIndex === false || $currentIndex === false) {
            return 'upcoming';
        }

        if ($this->audit_status === self::AUDIT_RESULT && $stageKey === self::AUDIT_RESULT) {
            return 'completed';
        }

        if ($stageIndex < $currentIndex) {
            return 'completed';
        }

        if ($stageIndex === $currentIndex) {
            return 'current';
        }

        return 'upcoming';
    }

    /**
     * @return list<array{key: string, label: string, description: string, state: string, children: list<mixed>}>
     */
    public function auditTimelineTree(): array
    {
        $stages = self::auditStages();
        $tree = [];

        foreach (self::auditStageKeys() as $key) {
            $tree[] = [
                'key' => $key,
                'label' => $stages[$key]['label'],
                'description' => $stages[$key]['description'],
                'state' => $this->auditStageState($key),
                'children' => [],
            ];
        }

        return $tree;
    }
}
