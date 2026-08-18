<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Planter\Auth\LoginController as PlanterLoginController;
use App\Http\Controllers\Planter\Auth\RegisterController;
use App\Http\Controllers\Planter\Auth\SetPasswordController;
use App\Http\Controllers\Planter\DashboardController as PlanterDashboardController;
use App\Http\Controllers\Planter\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('planter.login'));

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
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:web', 'active'])->group(function () {
        Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('planters/approval-lobby', [PlanterController::class, 'approvalLobby'])->name('planters.approval-lobby');
        Route::resource('planters', PlanterController::class);
        Route::post('planters/{planter}/approve', [PlanterController::class, 'approve'])->name('planters.approve');
        Route::post('planters/{planter}/reject', [PlanterController::class, 'reject'])->name('planters.reject');
        Route::get('planters/{planter}/document', [PlanterController::class, 'document'])->name('planters.document');
        Route::get('planters/{planter}/qr.png', [PlanterController::class, 'qr'])->name('planters.qr');

        Route::middleware('admin')->group(function () {
            Route::resource('users', UserController::class);
        });
    });
});
