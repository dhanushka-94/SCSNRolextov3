<?php

use App\Http\Controllers\Admin\AboutSystemController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\ChangelogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\PlanterController;
use App\Http\Controllers\Admin\RdoDivisionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CertificateVerifyController;
use App\Http\Controllers\Planter\Auth\LoginController as PlanterLoginController;
use App\Http\Controllers\Planter\Auth\RegisterController;
use App\Http\Controllers\Planter\Auth\SetPasswordController;
use App\Http\Controllers\Planter\CertificateController as PlanterCertificateController;
use App\Http\Controllers\Planter\DashboardController as PlanterDashboardController;
use App\Http\Controllers\Planter\ProfileController;
use App\Http\Controllers\PartnerHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', PartnerHomeController::class)->name('partner.home');

Route::get('verify', [CertificateVerifyController::class, 'form'])->name('verify.form');
Route::post('verify', [CertificateVerifyController::class, 'lookup'])->name('verify.lookup');
Route::get('verify/{token}', [CertificateVerifyController::class, 'show'])->name('verify.show');
Route::get('verify/{token}/qr.png', [CertificateVerifyController::class, 'qr'])->name('verify.qr');

Route::get('register/form.pdf', [RegisterController::class, 'formPdf'])->name('planter.register.form');

Route::middleware('guest:planter')->group(function () {
    Route::get('login', [PlanterLoginController::class, 'create'])->name('planter.login');
    Route::post('login', [PlanterLoginController::class, 'store'])->name('planter.login.store');
    Route::get('register', [RegisterController::class, 'create'])->name('planter.register');
    Route::post('register', [RegisterController::class, 'store'])->name('planter.register.store');
    Route::post('register/offline', [RegisterController::class, 'storeOffline'])->name('planter.register.offline');
    Route::get('register/submitted', [RegisterController::class, 'submitted'])->name('planter.register.submitted');
    Route::get('set-password', [SetPasswordController::class, 'create'])->name('planter.password.create');
    Route::post('set-password', [SetPasswordController::class, 'store'])->name('planter.password.store');
});

Route::middleware('auth:planter')->group(function () {
    Route::post('logout', [PlanterLoginController::class, 'destroy'])->name('planter.logout');
    Route::get('dashboard', PlanterDashboardController::class)->name('planter.dashboard');
    Route::get('profile', [ProfileController::class, 'edit'])->name('planter.profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('planter.profile.update');
    Route::get('profile/qr.png', [ProfileController::class, 'qrPng'])->name('planter.profile.qr');
    Route::get('profile/qr/download.{format}', [ProfileController::class, 'downloadQr'])
        ->whereIn('format', ['png', 'svg'])
        ->name('planter.profile.qr.download');
    Route::get('certificate', [PlanterCertificateController::class, 'show'])->name('planter.certificate');
    Route::get('certificate/print', [PlanterCertificateController::class, 'print'])->name('planter.certificate.print');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:web', 'active'])->group(function () {
        Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('about-system', AboutSystemController::class)->name('about-system');
        Route::get('changelog', ChangelogController::class)->name('changelog');
        Route::redirect('planters/approval-lobby', '/admin/planters/registration-lobby');
        Route::get('planters/registration-lobby', [PlanterController::class, 'approvalLobby'])->name('planters.registration-lobby');
        Route::get('planters/rejected', [PlanterController::class, 'rejected'])->name('planters.rejected');
        Route::resource('planters', PlanterController::class);
        Route::post('planters/{planter}/approve', [PlanterController::class, 'approve'])->name('planters.approve');
        Route::post('planters/{planter}/reject', [PlanterController::class, 'reject'])->name('planters.reject');
        Route::post('planters/{planter}/audits/first', [AuditController::class, 'sendFirst'])->name('planters.audits.send-first');
        Route::post('planters/{planter}/audits/final', [AuditController::class, 'sendFinal'])->name('planters.audits.send-final');
        Route::post('planters/{planter}/certificates', [AdminCertificateController::class, 'issue'])->name('planters.certificates.issue');
        Route::get('planters/{planter}/document', [PlanterController::class, 'document'])->name('planters.document');
        Route::get('planters/{planter}/certificate-document', [PlanterController::class, 'certificateDocument'])->name('planters.certificate-document');
        Route::get('planters/{planter}/qr.png', [PlanterController::class, 'qr'])->name('planters.qr');

        Route::get('audits/ongoing', [AuditController::class, 'ongoing'])->name('audits.ongoing');
        Route::get('audits/passed', [AuditController::class, 'passed'])->name('audits.passed');
        Route::get('audits/rejected', [AuditController::class, 'rejected'])->name('audits.rejected');
        Route::redirect('audits', '/admin/audits/ongoing')->name('audits.lobby');
        Route::get('audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
        Route::post('audits/{audit}/start', [AuditController::class, 'start'])->name('audits.start');
        Route::post('audits/{audit}/checklist', [AuditController::class, 'saveChecklist'])->name('audits.checklist.save');
        Route::post('audits/{audit}/submit', [AuditController::class, 'submit'])->name('audits.submit');
        Route::post('audits/{audit}/complete', [AuditController::class, 'complete'])->name('audits.complete');
        Route::post('audits/{audit}/reopen', [AuditController::class, 'reopen'])->name('audits.reopen');

        Route::get('certificates/issued', [AdminCertificateController::class, 'issued'])->name('certificates.issued');
        Route::get('certificates/revoked', [AdminCertificateController::class, 'revoked'])->name('certificates.revoked');
        Route::get('certificates/{certificate}', [AdminCertificateController::class, 'show'])->name('certificates.show');
        Route::get('certificates/{certificate}/print', [AdminCertificateController::class, 'print'])->name('certificates.print');
        Route::get('certificates/{certificate}/qr.png', [AdminCertificateController::class, 'qr'])->name('certificates.qr');
        Route::post('certificates/{certificate}/revoke', [AdminCertificateController::class, 'revoke'])->name('certificates.revoke');

        Route::middleware('admin')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('districts', DistrictController::class);
            Route::resource('rdo-divisions', RdoDivisionController::class);
        });
    });
});
