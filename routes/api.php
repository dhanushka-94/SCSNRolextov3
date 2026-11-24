<?php

use App\Http\Controllers\Api\V1\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Api\V1\Admin\SmsTemplateController as AdminSmsTemplateController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Planter\PortalController;
use App\Http\Controllers\Api\V1\PlanterRegistrationController;
use App\Http\Controllers\Api\V1\ReferenceController;
use App\Http\Controllers\Api\V1\Staff\AuditController;
use App\Http\Controllers\Api\V1\Staff\CertificateController;
use App\Http\Controllers\Api\V1\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Api\V1\Staff\PlanterController as StaffPlanterController;
use App\Http\Controllers\Api\V1\Staff\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SPPNR mobile API (v1)
|--------------------------------------------------------------------------
| Base URL: /api/v1
| Auth: Authorization: Bearer <token> (Laravel Sanctum personal access tokens)
| Planter tokens are issued by POST /v1/auth/planter/login;
| staff tokens by POST /v1/auth/staff/login.
*/

Route::prefix('v1')->group(function () {

    // Public: app details, reference data and maintenance state.
    Route::get('meta', [ReferenceController::class, 'meta']);
    Route::get('reference/locations', [ReferenceController::class, 'locations']);
    Route::get('reference/options', [ReferenceController::class, 'options']);

    // Sign-in and registration (rate limited to slow down guessing and spam).
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('auth/planter/login', [AuthController::class, 'planterLogin']);
        Route::post('auth/staff/login', [AuthController::class, 'staffLogin']);

        Route::post('registrations', [PlanterRegistrationController::class, 'register']);
        Route::post('registrations/offline', [PlanterRegistrationController::class, 'registerOffline']);
        Route::post('registrations/password', [PlanterRegistrationController::class, 'setPassword']);
        Route::post('registrations/status', [PlanterRegistrationController::class, 'status']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        // Any signed-in account (planter or staff).
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-others', [AuthController::class, 'logoutOthers']);
        Route::post('auth/password', [AuthController::class, 'changePassword']);

        // Planter portal.
        Route::middleware('api.planter')->prefix('planter')->group(function () {
            Route::get('dashboard', [PortalController::class, 'dashboard']);
            Route::get('profile', [PortalController::class, 'profile']);
            Route::put('profile', [PortalController::class, 'updateProfile']);
            Route::get('certificate', [PortalController::class, 'certificate']);
        });

        // Staff: field officers, auditors, RRISL / RDD / Ministry officers and admins.
        Route::middleware('api.staff')->prefix('staff')->group(function () {
            Route::get('dashboard', StaffDashboardController::class);

            Route::get('planters', [StaffPlanterController::class, 'index']);
            Route::post('planters', [StaffPlanterController::class, 'store']);
            Route::get('planters/{planter}', [StaffPlanterController::class, 'show']);
            Route::match(['put', 'patch'], 'planters/{planter}', [StaffPlanterController::class, 'update']);
            Route::delete('planters/{planter}', [StaffPlanterController::class, 'destroy']);
            Route::post('planters/{planter}/approve', [StaffPlanterController::class, 'approve']);
            Route::post('planters/{planter}/reject', [StaffPlanterController::class, 'reject']);
            Route::get('planters/{planter}/application-document', [StaffPlanterController::class, 'applicationDocument']);
            Route::get('planters/{planter}/prior-certificate-document', [StaffPlanterController::class, 'priorCertificateDocument']);
            Route::post('planters/{planter}/sms/resend', [StaffPlanterController::class, 'resendSms']);
            Route::post('planters/{planter}/audits/first', [StaffPlanterController::class, 'dispatchFirstAudit']);
            Route::post('planters/{planter}/audits/final', [StaffPlanterController::class, 'dispatchSecondAudit']);

            Route::get('audits', [AuditController::class, 'index']);
            Route::get('audits/{audit}', [AuditController::class, 'show']);
            Route::post('audits/{audit}/start', [AuditController::class, 'start']);
            Route::put('audits/{audit}/checklist', [AuditController::class, 'saveChecklist']);
            Route::post('audits/{audit}/submit', [AuditController::class, 'submit']);
            Route::post('audits/{audit}/complete', [AuditController::class, 'complete']);
            Route::post('audits/{audit}/reopen', [AuditController::class, 'reopen']);

            Route::get('certificates', [CertificateController::class, 'index']);
            Route::get('certificates/{certificate}', [CertificateController::class, 'show']);
            Route::post('planters/{planter}/certificates', [CertificateController::class, 'issue']);
            Route::post('certificates/{certificate}/revoke', [CertificateController::class, 'revoke']);

            Route::get('sms-templates', [AdminSmsTemplateController::class, 'index']);
            Route::post('sms-templates/send-samples', [AdminSmsTemplateController::class, 'sendSamples']);

            // Offline queue upload from the app.
            Route::post('sync', [SyncController::class, 'store']);
        });

        // Administrators (and the Developer).
        Route::middleware('api.staff:admin')->prefix('admin')->group(function () {
            Route::get('users', [AdminUserController::class, 'index']);
            Route::post('users', [AdminUserController::class, 'store']);
            Route::put('users/{user}', [AdminUserController::class, 'update']);
            Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
        });

        // Super Admin (Developer) only: same switch as the Settings page.
        Route::middleware('api.staff:developer')->prefix('admin')->group(function () {
            Route::get('maintenance', [AdminMaintenanceController::class, 'show']);
            Route::put('maintenance', [AdminMaintenanceController::class, 'update']);
        });
    });
});
