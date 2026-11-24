<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'role_label' => User::roles()[$this->role] ?? $this->role,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at,
            'permissions' => self::permissions($this->resource),
        ];
    }

    /**
     * Lets the app show or hide features without re-implementing the role matrix.
     *
     * @return array<string, bool>
     */
    public static function permissions(User $user): array
    {
        return [
            'view_registry' => $user->canViewRegistry(),
            'review_registrations' => $user->canReviewRegistrations(),
            'manage_planter_records' => $user->canManagePlanterRecords(),
            'delete_planters' => $user->canDeletePlanters(),
            'dispatch_audits' => $user->canDispatchAudits(),
            'work_first_audit' => $user->canWorkAuditRound(\App\Models\PlanterAudit::ROUND_FIRST),
            'work_second_audit' => $user->canWorkAuditRound(\App\Models\PlanterAudit::ROUND_FINAL),
            'view_audits' => $user->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FIRST)
                && $user->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FINAL),
            'manage_certificates' => $user->canManageCertificates(),
            'resend_planter_sms' => $user->canResendPlanterSms(),
            'send_sms_samples' => $user->canSendSmsSamples(),
            'manage_master_data' => $user->canManageMasterData(),
            'manage_users' => $user->isAdmin(),
            'manage_settings' => $user->canManageSettings(),
            'view_api_docs' => $user->canViewApiDocs(),
        ];
    }
}
