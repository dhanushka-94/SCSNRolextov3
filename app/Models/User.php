<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Super Admin: every administrator power plus Settings and the API documentation. */
    public const ROLE_DEVELOPER = 'developer';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_RRISL_OFFICER = 'rrisl_officer';

    public const ROLE_RDD_OFFICER = 'rdd_officer';

    public const ROLE_MINISTRY_OFFICER = 'ministry_officer';

    public const ROLE_FIELD_OFFICER = 'field_officer';

    public const ROLE_FIRST_AUDITOR = 'first_auditor';

    public const ROLE_SECOND_AUDITOR = 'second_auditor';

    /** @deprecated Use ROLE_RRISL_OFFICER */
    public const ROLE_STAFF = self::ROLE_RRISL_OFFICER;

    /** @deprecated Use ROLE_SECOND_AUDITOR */
    public const ROLE_FINAL_AUDITOR = self::ROLE_SECOND_AUDITOR;

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

    /** Administrator-level access. The Developer (Super Admin) counts as an administrator too. */
    public function isAdmin(): bool
    {
        return in_array($this->role, self::adminRoles(), true);
    }

    public function isDeveloper(): bool
    {
        return $this->role === self::ROLE_DEVELOPER;
    }

    /** Settings (maintenance mode) and the API documentation: Developer only. */
    public function canManageSettings(): bool
    {
        return $this->isDeveloper();
    }

    public function canViewApiDocs(): bool
    {
        return $this->isDeveloper();
    }

    /**
     * Administrators cannot see, edit or remove the Developer account.
     */
    public function canBeManagedBy(User $actor): bool
    {
        return ! $this->isDeveloper() || $actor->isDeveloper();
    }

    /**
     * @return list<string>
     */
    public static function adminRoles(): array
    {
        return [self::ROLE_DEVELOPER, self::ROLE_ADMIN];
    }

    public function isRrislOfficer(): bool
    {
        return $this->role === self::ROLE_RRISL_OFFICER;
    }

    public function isRddOfficer(): bool
    {
        return $this->role === self::ROLE_RDD_OFFICER;
    }

    public function isMinistryOfficer(): bool
    {
        return $this->role === self::ROLE_MINISTRY_OFFICER;
    }

    public function isFieldOfficer(): bool
    {
        return $this->role === self::ROLE_FIELD_OFFICER;
    }

    /** @deprecated Use isRrislOfficer() */
    public function isStaff(): bool
    {
        return $this->isRrislOfficer();
    }

    public function isFirstAuditor(): bool
    {
        return $this->role === self::ROLE_FIRST_AUDITOR;
    }

    public function isSecondAuditor(): bool
    {
        return $this->role === self::ROLE_SECOND_AUDITOR;
    }

    /** @deprecated Use isSecondAuditor() */
    public function isFinalAuditor(): bool
    {
        return $this->isSecondAuditor();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isReadOnlyOfficer(): bool
    {
        return $this->isMinistryOfficer();
    }

    /** Approve / reject pending applications. */
    public function canReviewRegistrations(): bool
    {
        return $this->isAdmin() || $this->isRrislOfficer() || $this->isRddOfficer();
    }

    /** Create / edit planter records (not approve). */
    public function canManagePlanterRecords(): bool
    {
        return $this->isAdmin()
            || $this->isRrislOfficer()
            || $this->isRddOfficer()
            || $this->isFieldOfficer();
    }

    public function canDeletePlanters(): bool
    {
        return $this->isAdmin() || $this->isRrislOfficer();
    }

    public function canDispatchAudits(): bool
    {
        return $this->isAdmin() || $this->isRrislOfficer() || $this->isRddOfficer();
    }

    public function canWorkAuditRound(string $round): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match ($round) {
            PlanterAudit::ROUND_FIRST => $this->isFirstAuditor(),
            PlanterAudit::ROUND_FINAL => $this->isSecondAuditor(),
            default => false,
        };
    }

    public function canViewAuditRound(string $round): bool
    {
        // Admin + officers + both auditor roles can view First and Second status.
        if ($this->isAdmin()
            || $this->isRrislOfficer()
            || $this->isRddOfficer()
            || $this->isMinistryOfficer()
            || $this->isFieldOfficer()
            || $this->isFirstAuditor()
            || $this->isSecondAuditor()) {
            return true;
        }

        return $this->canWorkAuditRound($round);
    }

    public function canResendPlanterSms(): bool
    {
        return $this->isAdmin() || $this->isRrislOfficer() || $this->isRddOfficer();
    }

    public function canManageCertificates(): bool
    {
        return $this->isAdmin() || $this->isRrislOfficer();
    }

    public function canManageMasterData(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Sample SMS is only sent from the web Settings page (developer only).
     * The API no longer offers sample sending, so the flag stays false for every account.
     */
    public function canSendSmsSamples(): bool
    {
        return false;
    }

    public function canViewRegistry(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_DEVELOPER => 'Super Admin (Developer)',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_RRISL_OFFICER => 'RRISL Officer',
            self::ROLE_RDD_OFFICER => 'RDD Officer',
            self::ROLE_MINISTRY_OFFICER => 'Ministry Officer',
            self::ROLE_FIELD_OFFICER => 'Field Officer',
            self::ROLE_FIRST_AUDITOR => 'First Auditor',
            self::ROLE_SECOND_AUDITOR => 'Second Auditor',
        ];
    }

    /**
     * Roles the given user may assign. Only a Developer can create another Developer.
     *
     * @return array<string, string>
     */
    public static function assignableRoles(?User $actor): array
    {
        $roles = self::roles();

        if (! $actor?->isDeveloper()) {
            unset($roles[self::ROLE_DEVELOPER]);
        }

        return $roles;
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}
