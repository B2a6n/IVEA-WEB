<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleControlController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// Admin and tutor can read; anything that writes must go in a nested ->middleware('role:admin') group.
Route::middleware(['auth', 'role:admin,tutor'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('modulos', [ModuleControlController::class, 'index'])->name('modulos.index');

    Route::middleware('role:admin')->group(function (): void {
        // Each real change publishes a config version: throttled per user to keep the history bounded.
        Route::put('modulos', [ModuleControlController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('modulos.update');
    });
});
