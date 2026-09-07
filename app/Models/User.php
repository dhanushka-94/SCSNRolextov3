<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_STAFF = 'staff';

    public const ROLE_FIRST_AUDITOR = 'first_auditor';

    public const ROLE_FINAL_AUDITOR = 'final_auditor';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
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
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isFirstAuditor(): bool
    {
        return $this->role === self::ROLE_FIRST_AUDITOR;
    }

    public function isFinalAuditor(): bool
    {
        return $this->role === self::ROLE_FINAL_AUDITOR;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canDispatchAudits(): bool
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public function canWorkAuditRound(string $round): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match ($round) {
            \App\Models\PlanterAudit::ROUND_FIRST => $this->isFirstAuditor(),
            \App\Models\PlanterAudit::ROUND_FINAL => $this->isFinalAuditor(),
            default => false,
        };
    }

    public function canViewAuditRound(string $round): bool
    {
        return $this->canDispatchAudits() || $this->canWorkAuditRound($round);
    }

    public function canManageCertificates(): bool
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_STAFF => 'Staff',
            self::ROLE_FIRST_AUDITOR => 'First Auditor',
            self::ROLE_FINAL_AUDITOR => 'Final Auditor',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}
