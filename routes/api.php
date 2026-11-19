<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\MobileConfigController;
use App\Http\Controllers\Api\V1\MobileSyncController;
use Illuminate\Support\Facades\Route;

// API v1, consumed only by the mobile app. Throttling is keyed per authenticated user: field devices
// often share one NAT IP, so a per-IP limit would block legitimate users.
Route::prefix('v1/mobile')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function (): void {
        Route::get('config', MobileConfigController::class)->name('api.v1.mobile.config');
        Route::post('sync', MobileSyncController::class)->name('api.v1.mobile.sync');
    });
