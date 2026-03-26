<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeedBumpController;
use App\Http\Controllers\DeviceEventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoadEventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::get('/bumps', [SpeedBumpController::class, 'getAll']);
Route::post('/bumps/nearby', [SpeedBumpController::class, 'getNearby']);
Route::post('/bumps', [SpeedBumpController::class, 'store']);
Route::put('/bumps/{bump}', [SpeedBumpController::class, 'update']);
Route::delete('/bumps/{bump}', [SpeedBumpController::class, 'destroy']);
Route::post('/bumps/{bump}/report', [SpeedBumpController::class, 'report']);

// Public ingest for road events (devices without auth)
Route::post('/road-events/ingest', [RoadEventController::class, 'storePublic']);

// Statistics API (public)
Route::get('/stats', function () {
    return response()->json([
        'success' => true,
        'stats' => [
            'total_bumps' => \App\Models\SpeedBump::count(),
            'verified_bumps' => \App\Models\SpeedBump::where('is_verified', true)->count(),
            'alert_count' => 0, // This would be tracked per session
        ]
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // Device Events API
    Route::post('/events', [DeviceEventController::class, 'store']);
    Route::post('/events/batch', [DeviceEventController::class, 'batch']);

    // Road events (can be posted by devices)
    Route::apiResource('road-events', RoadEventController::class);

    // Reports API
    Route::post('/reports', [ReportController::class, 'store']);

    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // User settings
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'store']);
});
