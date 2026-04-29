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
// These use the app's normal user auth and do NOT require the ESP32 shared secret.
Route::middleware(['throttle:60,1', 'auth'])->group(function () {
    // Web-triggered scanning (request + status polling)
    Route::post('/rfid/scan/request', [RfidController::class, 'getCardUIDFromESP32Reader'])->name('api.rfid.scan.request');
    Route::get('/rfid/scan/status/{scanId}', [RfidController::class, 'checkScanRequestStatus'])->name('api.rfid.scan.status');

    // Recent logs JSON for dynamic UI (landlord-specific)
    Route::get('/rfid/recent-logs', [RfidController::class, 'recentLogsJson'])->name('api.rfid.recent-logs');
});

// Private storage serving route.
//
// Files are resolved from:
//   payment-proofs/     — storage/app/private (guarded via Payment ownership)
//   tenant-documents/     — storage/app/public (guarded via TenantDocument)
//   chat-attachments/     — storage/app/public (guarded via conversation membership)
//   anything else         — 404 (unlisted directories are never served)
//
// Cache-Control is set to "private, no-store" to prevent shared caches (CDNs,
// reverse proxies) from storing or serving one user's files to another.
Route::get('/storage/{path}', function (Request $request, $path) {

    // ── 1. Path traversal guard ───────────────────────────────────────────
    // Payment proofs use the non-public disk (storage/app/private); other guarded paths stay under app/public.
    $relativeFromRoute = ltrim(str_replace('\\', '/', $path), '/');

    $privateBase = realpath(storage_path('app/private'));
    $publicBase = realpath(storage_path('app/public'));

    $usePrivateDisk = str_starts_with($relativeFromRoute, 'payment-proofs/');
    $basePath = $usePrivateDisk ? $privateBase : $publicBase;

    abort_if($basePath === false, 404);

    $segmentPath = str_replace('/', DIRECTORY_SEPARATOR, $relativeFromRoute);
    $fullPath = realpath($basePath.DIRECTORY_SEPARATOR.$segmentPath);
    $basePrefix = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

    if ($fullPath === false || ! str_starts_with($fullPath, $basePrefix) || ! is_file($fullPath)) {
        abort(404);
    }

    // Relative path (forward slashes) used in DB columns and authorization lookups.
    $relativePath = ltrim(str_replace('\\', '/', substr($fullPath, strlen($basePath))), '/');

    // ── 2. Authorization ──────────────────────────────────────────────────
    $user = auth()->user();

    if (str_starts_with($relativePath, 'tenant-documents/')) {
        // Tenant documents: private — owner, their active/past landlord, or super_admin.
        abort_if(! $user, 401);

        $doc = \App\Models\TenantDocument::where('file_path', $relativePath)->first();
        abort_if(! $doc, 404);

        if (! $user->isSuperAdmin() && $doc->tenant_id !== $user->id) {
            abort_unless($user->isLandlord(), 403);

            $isAssigned = \App\Models\TenantAssignment::where('tenant_id', $doc->tenant_id)
                ->where('landlord_id', $user->id)
                ->exists();

            abort_unless($isAssigned, 403);
        }

    } elseif (str_starts_with($relativePath, 'payment-proofs/')) {
        // Payment proofs: private — the paying tenant, the bill's landlord, or super_admin.
        abort_if(! $user, 401);

        $payment = \App\Models\Payment::with('bill')->where('proof_image', $relativePath)->first();
        abort_if(! $payment, 404);

        $isOwner    = $payment->tenant_id === $user->id;
        $isLandlord = $payment->bill?->landlord_id === $user->id;

        abort_unless($isOwner || $isLandlord || $user->isSuperAdmin(), 403);

    } elseif (str_starts_with($relativePath, 'chat-attachments/')) {
        // Chat attachments: private — only participants of the conversation.
        abort_if(! $user, 401);

        $attachment = \App\Models\MessageAttachment::with('message')->where('file_path', $relativePath)->first();
        abort_if(! $attachment, 404);

        $conversationId = $attachment->message?->conversation_id;
        abort_if(! $conversationId, 404);

        $isParticipant = \App\Models\ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isParticipant || $user->isSuperAdmin(), 403);

    } else {
        // Unlisted directory — return 404 (not 403) to avoid leaking that the file exists.
        abort(404);
    }

    // ── 3. Serve file ─────────────────────────────────────────────────────
    return response()->file($fullPath, [
        'Content-Type'  => mime_content_type($fullPath),
        'Cache-Control' => 'private, no-store',
    ]);

})->where('path', '.*')->name('api.storage.fallback');
