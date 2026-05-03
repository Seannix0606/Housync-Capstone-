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
// All routes here require the X-ESP32-Key header to match ESP32_API_KEY in .env.
Route::middleware(['throttle:60,1', 'esp32.auth'])->group(function () {
    // Card scan — used by both the serial bridge (ESP32Reader.php) and WiFi mode firmware
    Route::post('/rfid-scan', [RfidController::class, 'scanCardDirect'])->name('api.rfid-scan');
    Route::post('/rfid/scan/direct', [RfidController::class, 'scanCardDirect'])->name('api.rfid.scan-direct');

    // Access verification (alternative endpoint if you want a verify-only flow)
    Route::post('/rfid/verify', [RfidController::class, 'verifyAccess'])->name('api.rfid.verify');

    // WiFi mode: ESP32 polls this every ~2 s to learn about pending web-scan requests
    Route::get('/rfid/scan/pending', [RfidController::class, 'getPendingScanRequest'])->name('api.rfid.scan.pending');
});

// Browser-facing RFID API routes (Blade dashboards use fetch(); needs `web` middleware
// so session cookies + CSRF apply — plain `api` stack has no session, so `auth` returned login HTML).
Route::middleware(['web', 'throttle:60,1', 'auth'])->group(function () {
    Route::post('/rfid/scan/request', [RfidController::class, 'getCardUIDFromESP32Reader'])->name('api.rfid.scan.request');
    Route::get('/rfid/scan/status/{scanId}', [RfidController::class, 'checkScanRequestStatus'])->name('api.rfid.scan.status');

    // Latest scanned card UID (card-registration UI — landlord/security/create.blade.php)
    Route::get('/rfid/latest-uid', [RfidController::class, 'getLatestCardUID'])->name('api.rfid.latest-uid');

    // Recent logs JSON for dynamic UI (landlord-specific)
    Route::get('/rfid/recent-logs', [RfidController::class, 'recentLogsJson'])->name('api.rfid.recent-logs');
    /** Same handler as GET /rfid/latest-uid but for logged-in browsers (ESP32 route stays API-key-only). */
    Route::get('/rfid/web/latest-uid', [RfidController::class, 'getLatestCardUID'])->name('api.rfid.web.latest-uid');
});

// Private storage serving route.
//
// Files are resolved from:
//   payment-proofs/                          — private (guarded via Payment ownership)
//   landlord-instapay-quick-response-codes/ — private (guarded: landlord owner or their tenants)
//   tenant-documents/                        — public disk (guarded via TenantDocument)
//   chat-attachments/                        — public disk (guarded via conversation membership)
//   properties/                              — public disk (no auth required; public property images)
//   anything else                            — 404 (unlisted directories are never served)
//
// Cache-Control is set to "private, no-store" to prevent shared caches (CDNs,
// reverse proxies) from storing or serving one user's files to another.
//
// `web` middleware is required so session cookies authenticate `<img src="/api/storage/...">`
// requests from logged-in landlords and tenants.
Route::middleware(['web'])->group(function () {
Route::get('/storage/{path}', function (Request $request, $path) {

    // ── 1. Path traversal guard ───────────────────────────────────────────
    // Payment proofs use the non-public disk (storage/app/private); other guarded paths stay under app/public.
    $relativeFromRoute = ltrim(str_replace('\\', '/', $path), '/');

    $privateBase = realpath(storage_path('app/private'));
    $publicBase = realpath(storage_path('app/public'));

    $segmentPath = str_replace('/', DIRECTORY_SEPARATOR, $relativeFromRoute);

    $fullPath = false;
    $basePath = null;

    if (str_starts_with($relativeFromRoute, 'payment-proofs/')
        || str_starts_with($relativeFromRoute, 'landlord-instapay-quick-response-codes/')) {
        // Private disk first; payment proofs may fall back to public (legacy store).
        foreach ([$privateBase, $publicBase] as $tryBase) {
            if ($tryBase === false) {
                continue;
            }
            $candidate = realpath($tryBase.DIRECTORY_SEPARATOR.$segmentPath);
            $prefix = rtrim($tryBase, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            if ($candidate !== false && str_starts_with($candidate, $prefix) && is_file($candidate)) {
                $fullPath = $candidate;
                $basePath = $tryBase;
                break;
            }
        }
        abort_if($fullPath === false || $basePath === null, 404);
    } else {
        $basePath = $publicBase;
        abort_if($basePath === false, 404);
        $fullPath = realpath($basePath.DIRECTORY_SEPARATOR.$segmentPath);
        $basePrefix = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        abort_if(
            $fullPath === false || ! str_starts_with($fullPath, $basePrefix) || ! is_file($fullPath),
            404
        );
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

    } elseif (str_starts_with($relativePath, 'landlord-instapay-quick-response-codes/')) {
        abort_if(! $user, 401);

        $landlordOwningCode = \App\Models\User::query()
            ->where('role', 'landlord')
            ->where('landlord_instapay_quick_response_code_image_path', $relativePath)
            ->first();
        abort_if(! $landlordOwningCode, 404);

        $isLandlordOwner = $landlordOwningCode->id === $user->id;
        $tenantHasBillFromLandlord = \App\Models\Bill::query()
            ->where('landlord_id', $landlordOwningCode->id)
            ->where('tenant_id', $user->id)
            ->exists();

        abort_unless($isLandlordOwner || $tenantHasBillFromLandlord || $user->isSuperAdmin(), 403);

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

    } elseif (str_starts_with($relativePath, 'properties/')) {
        // Property images: public — no authentication required.
        // These are served from the public disk and are intentionally world-readable
        // (shown on the explore page to unauthenticated visitors).
        // No auth check needed; the path traversal guard above already ensures the
        // resolved file stays within storage/app/public.

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
});
