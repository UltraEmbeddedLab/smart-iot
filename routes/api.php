<?php declare(strict_types=1);

use App\Http\Controllers\Api\V1\DeviceConfigController;
use App\Http\Controllers\Api\V1\DeviceHeartbeatController;
use App\Http\Controllers\Api\V1\DeviceProvisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('provision', DeviceProvisionController::class)
        ->middleware('throttle:provision');

    Route::middleware(['device.auth', 'throttle:device-api'])->group(function (): void {
        Route::post('heartbeat', DeviceHeartbeatController::class);
        Route::get('config/{device:device_id}', DeviceConfigController::class);
    });
});
