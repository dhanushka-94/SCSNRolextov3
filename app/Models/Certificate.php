<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    public const STATUS_ISSUED = 'issued';

    public const STATUS_REVOKED = 'revoked';

    public const OUTCOME_CERTIFIED = 'certified';

    public const OUTCOME_CONDITIONAL = 'conditional';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'planter_id',
        'certificate_number',
        'public_token',
        'status',
        'outcome',
        'is_current',
        'issued_by',
        'revoked_by',
        'issued_at',
        'revoked_at',
        'revoke_reason',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $certificate): void {
            if (blank($certificate->public_token)) {
                $certificate->public_token = (string) Str::uuid();
            }
        });
    }

    public function planter(): BelongsTo
    {
        return $this->belongsTo(Planter::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_REVOKED => 'Revoked',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function outcomes(): array
    {
        return [
            self::OUTCOME_CERTIFIED => 'Certified',
            self::OUTCOME_CONDITIONAL => 'Conditional certification',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function outcomeLabel(): string
    {
        return self::outcomes()[$this->outcome] ?? $this->outcome;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function verifyUrl(): string
    {
        return url('/verify/'.$this->public_token);
    }

    public function publicVerificationLabel(): string
    {
        if ($this->isRevoked()) {
            return 'Revoked';
        }

        return $this->outcome === self::OUTCOME_CONDITIONAL
            ? 'Conditionally certified'
            : 'Valid certificate';
    }
}
