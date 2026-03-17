<?php

use App\Http\Controllers\RfidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ESP32 device callback API routes (secured with shared ESP32 key)
Route::middleware(['throttle:60,1', 'esp32.auth'])->group(function () {
    Route::post('/rfid/verify', [RfidController::class, 'verifyAccess'])->name('api.rfid.verify');
    Route::post('/rfid-scan', [RfidController::class, 'scanCardDirect'])->name('api.rfid-scan'); // For ESP32Reader.php Activity Logs
    Route::post('/rfid/scan/direct', [RfidController::class, 'scanCardDirect'])->name('api.rfid.scan-direct');
    Route::get('/rfid/latest-uid', [RfidController::class, 'getLatestCardUID'])->name('api.rfid.latest-uid'); // NEW: Get latest UID from ESP32Reader.php
});

// Browser-facing RFID API routes (web-triggered scans + landlord dashboards)
// These stay on the app's normal auth path and do NOT require the ESP32 shared secret.
Route::middleware(['throttle:60,1'])->group(function () {
    // Web-triggered scanning (request + status polling)
    Route::post('/rfid/scan/request', [RfidController::class, 'getCardUIDFromESP32Reader'])->name('api.rfid.scan.request');
    Route::get('/rfid/scan/status/{scanId}', [RfidController::class, 'checkScanRequestStatus'])->name('api.rfid.scan.status');

    // Recent logs JSON for dynamic UI (landlord-specific)
    Route::get('/rfid/recent-logs', [RfidController::class, 'recentLogsJson'])->name('api.rfid.recent-logs');
});

// Public storage serving route (no authentication required)
Route::get('/storage/{path}', function ($path) {
    $basePath = realpath(storage_path('app/public'));

    if ($basePath === false) {
        abort(404, "File not found.");
    }

    $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $path);
    $basePrefix = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    // Prevent path traversal and verify it's a valid file within the intended directory
    if ($fullPath === false || !str_starts_with($fullPath, $basePrefix) || !is_file($fullPath)) {
        abort(404, "File not found.");
    }

    $mimeType = mime_content_type($fullPath);

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*')->name('api.storage.fallback');
