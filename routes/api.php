<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidController;

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

// ESP32 RFID API Routes (with rate limiting for security)
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/rfid/verify', [RfidController::class, 'verifyAccess'])->name('api.rfid.verify');
    Route::post('/rfid-scan', [RfidController::class, 'scanCardDirect'])->name('api.rfid-scan'); // For ESP32Reader.php Activity Logs
    Route::post('/rfid/scan/direct', [RfidController::class, 'scanCardDirect'])->name('api.rfid.scan-direct');
    Route::get('/rfid/latest-uid', [RfidController::class, 'getLatestCardUID'])->name('api.rfid.latest-uid'); // NEW: Get latest UID from ESP32Reader.php
    Route::post('/rfid/generate-uid', [RfidController::class, 'generateCardUID'])->name('api.rfid.generate-uid'); // Fallback: Simple UID generator
    // Web-triggered scanning (request + status polling)
    Route::post('/rfid/scan/request', [RfidController::class, 'getCardUIDFromESP32Reader'])->name('api.rfid.scan.request');
    Route::get('/rfid/scan/status/{scanId}', [RfidController::class, 'checkScanRequestStatus'])->name('api.rfid.scan.status');
    // Recent logs JSON for dynamic UI
    Route::get('/rfid/recent-logs', [RfidController::class, 'recentLogsJson'])->name('api.rfid.recent-logs');
    Route::post('/rfid/test', [RfidController::class, 'testConnection'])->name('api.rfid.test');
    // Debug endpoint for testing deactivated card behavior
    Route::post('/rfid/test-deactivated', [RfidController::class, 'testDeactivatedCard'])->name('api.rfid.test-deactivated');
    Route::get('/system-info', function() {
        return response()->json([
            'success' => true,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connected' => true,
            'timestamp' => now()->toISOString()
        ]);
    })->name('api.system-info'); // For ESP32Reader.php connection test
});

// Public storage serving route (no authentication required)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    // Debug info
    if (request()->has('debug')) {
        return response()->json([
            'path' => $path,
            'fullPath' => $fullPath,
            'exists' => file_exists($fullPath),
            'is_file' => is_file($fullPath),
            'is_readable' => is_readable($fullPath),
            'size' => file_exists($fullPath) ? filesize($fullPath) : 'N/A',
            'mime' => file_exists($fullPath) ? mime_content_type($fullPath) : 'N/A'
        ]);
    }
    
    if (!file_exists($fullPath)) {
        abort(404, "File not found: $fullPath");
    }
    
    $mimeType = mime_content_type($fullPath);
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=3600'
    ]);
})->where('path', '.*')->name('api.storage.fallback');

