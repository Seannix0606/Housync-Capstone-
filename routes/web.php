<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\Landlord\RegistrationController as LandlordRegistrationController;
use App\Http\Controllers\Landlord\SettingsController as LandlordSettingsController;
use App\Http\Controllers\Landlord\TenantController as LandlordTenantController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RfidController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\LandlordVerificationController as SuperAdminLandlordVerificationController;
use App\Http\Controllers\SuperAdmin\PropertyController as SuperAdminPropertyController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\Staff\ProfileController as StaffProfileController;
use App\Http\Controllers\Tenant\AnnouncementController as TenantAnnouncementController;
use App\Http\Controllers\Tenant\ApplicationController as TenantApplicationController;
use App\Http\Controllers\Tenant\BillingController as TenantBillingController;
use App\Http\Controllers\Tenant\ChatController as TenantChatController;
use App\Http\Controllers\Tenant\LeaseController as TenantLeaseController;
use App\Http\Controllers\Tenant\MaintenanceController as TenantMaintenanceController;
use App\Http\Controllers\Tenant\ProfileController as TenantProfileController;
use App\Http\Controllers\TenantAssignmentController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::get('/register', fn () => view('register'))->name('register');
    Route::post('/register', 'register')->name('register.post');
    Route::post('/logout', 'logout')->name('logout');
});

// Public Explore (Property Listings)
Route::controller(ExploreController::class)->group(function () {
    Route::get('/explore', 'index')->name('explore');
    Route::get('/property/{slug}', 'show')->name('property.show');
});

// Public Landlord Registration & Status
Route::controller(LandlordRegistrationController::class)->prefix('landlord')->name('landlord.')->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'storeRegistration')->name('register.store');
    Route::get('/pending', 'pending')->name('pending');
    Route::get('/rejected', 'rejected')->name('rejected');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::controller(SuperAdminPropertyController::class)->group(function () {
        Route::get('/apartments', 'apartments')->name('apartments');
    });

    Route::controller(SuperAdminUserController::class)->group(function () {
        Route::get('/users', 'index')->name('users');
        Route::get('/users/create', 'create')->name('create-user');
        Route::post('/users', 'store')->name('store-user');
        Route::get('/users/{id}', 'edit')->name('edit-user');
        Route::put('/users/{id}', 'update')->name('update-user');
        Route::delete('/users/{id}', 'destroy')->name('delete-user');
    });

    Route::controller(SuperAdminLandlordVerificationController::class)->group(function () {
        Route::get('/pending-landlords', 'pendingLandlords')->name('pending-landlords');
        Route::post('/approve-landlord/{id}', 'approveLandlord')->name('approve-landlord');
        Route::post('/reject-landlord/{id}', 'rejectLandlord')->name('reject-landlord');
        Route::get('/landlords/{id}/documents', 'reviewLandlordDocuments')->name('landlord-docs');
        Route::post('/landlord-documents/{docId}/verify', 'verifyLandlordDocument')->name('verify-landlord-document');
    });

    Route::controller(SuperAdminSettingsController::class)->group(function () {
        Route::get('/settings', 'settings')->name('settings');
        Route::post('/settings', 'updateSettings')->name('settings.update');
        Route::post('/settings/{group}', 'updateSettingsGroup')->name('settings.group.update');
        Route::get('/check-dark-mode', 'checkDarkMode')->name('check-dark-mode');
    });
});

/*
|--------------------------------------------------------------------------
| Landlord Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['role:landlord', 'verified'])->prefix('landlord')->name('landlord.')->group(function () {

    // Dashboard
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    // Tenants & Tenant History
    Route::controller(LandlordTenantController::class)->group(function () {
        Route::get('/tenants', 'tenants')->name('tenants');
        Route::get('/tenant-history', 'tenantHistory')->name('tenant-history');
        Route::get('/tenant-history/export-csv', 'exportTenantHistoryCSV')->name('tenant-history.export-csv');
    });

    // Settings
    Route::controller(LandlordSettingsController::class)->group(function () {
        Route::get('/settings', 'settings')->name('settings');
        Route::put('/settings', 'updateSettings')->name('settings.update');
        Route::put('/settings/password', 'updatePassword')->name('settings.password');
    });

    // Apartments (Properties)
    Route::controller(\App\Http\Controllers\Landlord\PropertyController::class)->group(function () {
        Route::get('/apartments', 'apartments')->name('apartments');
        Route::get('/apartments/create', 'createApartment')->name('create-apartment');
        Route::post('/apartments', 'storeApartment')->name('store-apartment');
        Route::get('/apartments/{id}/edit', 'editApartment')->name('edit-apartment');
        Route::put('/apartments/{id}', 'updateApartment')->name('update-apartment');
        Route::delete('/apartments/{id}', 'deleteApartment')->name('delete-apartment');
        Route::get('/apartments/{id}/details', 'getApartmentDetails')->name('apartment-details')->whereNumber('id');
        Route::get('/apartments/{id}/units', 'getApartmentUnits')->name('apartment-units')->whereNumber('id');
    });

    // Units
    Route::controller(\App\Http\Controllers\Landlord\UnitController::class)->group(function () {
        Route::get('/units/create', 'createUnit')->name('create-unit');
        Route::get('/units/{apartmentId?}', 'units')->name('units')->whereNumber('apartmentId');
        Route::get('/units/{id}/details', 'getUnitDetails')->name('unit-details')->whereNumber('id');
        Route::put('/units/{id}', 'updateUnit')->name('update-unit')->whereNumber('id');
        Route::delete('/units/{id}', 'deleteUnit')->name('delete-unit')->whereNumber('id');

        // Apartment-specific Unit Operations
        Route::get('/apartments/{apartmentId}/units/create', 'createUnit')->name('create-unit-for-apartment')->whereNumber('apartmentId');
        Route::get('/apartments/{apartmentId}/units/create-multiple', 'createMultipleUnits')->name('create-multiple-units')->whereNumber('apartmentId');
        Route::post('/apartments/{apartmentId}/units', 'storeUnit')->name('store-unit')->whereNumber('apartmentId');
        Route::post('/apartments/{apartmentId}/units/bulk', 'storeBulkUnits')->name('store-bulk-units')->whereNumber('apartmentId');
        Route::get('/apartments/{apartmentId}/units/bulk-edit', 'bulkEditUnits')->name('bulk-edit-units')->whereNumber('apartmentId');
        Route::post('/apartments/{apartmentId}/units/finalize-bulk', 'finalizeBulkUnits')->name('finalize-bulk-units')->whereNumber('apartmentId');
        Route::post('/apartments/{apartmentId}/units/json', 'storeApartmentUnit')->name('store-apartment-unit-json')->whereNumber('apartmentId');
    });

    // Tenant Assignments
    Route::controller(TenantAssignmentController::class)->group(function () {
        Route::get('/tenant-assignments', 'index')->name('tenant-assignments');
        Route::post('/units/{unitId}/assign-tenant', 'store')->name('store-tenant-assignment');
        Route::get('/tenant-assignments/{id}', 'show')->name('assignment-details');
        Route::put('/tenant-assignments/{id}/status', 'updateStatus')->name('update-assignment-status');
        Route::post('/tenant-assignments/{id}/reassign', 'reassign')->name('reassign-tenant');
        Route::delete('/tenant-assignments/{id}', 'destroy')->name('delete-tenant-assignment');
        Route::get('/tenant-assignments/{id}/credentials', 'getCredentials')->name('get-credentials');
        Route::get('/available-units', 'getAvailableUnits')->name('available-units');
        Route::get('/download-document/{documentId}', 'downloadDocument')->name('download-document');
        Route::post('/tenant-assignments/{id}/approve', 'approveApplication')->name('approve-application');
        Route::post('/tenant-assignments/{id}/reject', 'rejectApplication')->name('reject-application');
    });

    // Staff Management
    Route::controller(\App\Http\Controllers\Landlord\StaffController::class)->group(function () {
        Route::get('/staff', 'index')->name('staff');
        Route::post('/staff/add', 'addStaff')->name('add-staff');
        Route::get('/staff/by-type/{staffType}', 'getStaffByType')->name('staff-by-type');
        Route::get('/staff/create', 'create')->name('create-staff');
        Route::get('/units/{unitId}/assign-staff', 'create')->name('assign-staff');
        Route::post('/staff', 'store')->name('store-staff');
        Route::get('/staff/{id}', 'show')->name('staff-details');
        Route::put('/staff/{id}/status', 'updateStatus')->name('update-staff-status');
        Route::delete('/staff/{id}', 'destroy')->name('delete-staff');
        Route::get('/staff/{id}/credentials', 'getCredentials')->name('get-staff-credentials');
    });

    // Billing & Payments
    Route::controller(BillingController::class)->group(function () {
        Route::get('/payments', 'landlordIndex')->name('payments');
        Route::get('/billing/create', 'create')->name('billing.create');
        Route::post('/billing', 'store')->name('billing.store');
        Route::get('/billing/{id}', 'show')->name('billing.show');
        Route::post('/billing/{id}/payment', 'recordPayment')->name('billing.record-payment');
        Route::post('/billing/{id}/mark-paid', 'markAsPaid')->name('billing.mark-paid');
        Route::delete('/billing/{id}', 'destroy')->name('billing.destroy');
    });

    // RFID Security
    Route::controller(RfidController::class)->prefix('security')->name('security.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/cards/create', 'create')->name('create-card');
        Route::post('/cards', 'store')->name('store-card');
        Route::get('/cards/{id}', 'show')->name('card-details');
        Route::put('/cards/{id}/toggle-status', 'toggleStatus')->name('toggle-card-status');
        Route::get('/cards/{id}/reassign', 'reassignForm')->name('reassign-card-form');
        Route::post('/cards/{id}/reassign', 'reassign')->name('reassign-card');
        Route::get('/access-logs', 'accessLogs')->name('access-logs');
    });

    // Chat & Messaging
    Route::controller(ChatController::class)->group(function () {
        Route::get('/messages', 'landlordIndex')->name('chat');
        Route::get('/messages/{id}', 'show')->name('chat.show');
        Route::post('/messages/start-with-tenant', 'startWithTenant')->name('chat.start-with-tenant');
        Route::post('/messages/{id}/send', 'sendMessage')->name('chat.send');
        Route::get('/messages/{id}/fetch', 'getMessages')->name('chat.fetch');
        Route::post('/messages/{id}/read', 'markAsRead')->name('chat.mark-read');
        Route::post('/messages/{id}/ticket-status', 'updateTicketStatus')->name('chat.ticket-status');
        Route::get('/api/conversations', 'getConversations')->name('chat.conversations');
        Route::get('/api/unread-count', 'getUnreadCount')->name('chat.unread-count');
        Route::get('/api/tenants-list', 'getTenantsList')->name('chat.tenants-list');
    });

    // Maintenance
    Route::controller(MaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/assign-staff', 'assignStaff')->name('assign-staff');
        Route::post('/{id}/update-status', 'updateStatus')->name('update-status');
        Route::post('/{id}/update-notes', 'updateNotes')->name('update-notes');
        Route::post('/{id}/cancel', 'cancel')->name('cancel');
        Route::post('/{id}/comment', 'addComment')->name('add-comment');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Announcements
    Route::controller(AnnouncementController::class)->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::post('/{id}/publish', 'publish')->name('publish');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Billing: verify tenant payment proof
    Route::post('/billing/payments/{paymentId}/verify', [BillingController::class, 'verifyPayment'])->name('billing.verify-payment');

    // Reports & Analytics
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export-financial', 'exportFinancial')->name('export-financial');
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['role:tenant', 'verified'])->prefix('tenant')->name('tenant.')->group(function () {

    // Dashboard & Profile
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::controller(TenantProfileController::class)->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/update-password', 'updatePassword')->name('update-password');
        Route::get('/upload-documents', 'uploadDocuments')->name('upload-documents');
        Route::post('/upload-documents', 'storeDocuments')->name('store-documents');
        Route::get('/download-document/{documentId}', 'downloadDocument')->name('download-document');
        Route::delete('/delete-document/{documentId}', 'deleteDocument')->name('delete-document');
    });

    Route::controller(TenantLeaseController::class)->group(function () {
        Route::get('/lease', 'lease')->name('lease');
    });

    Route::controller(TenantApplicationController::class)->group(function () {
        Route::post('/apply/{propertyId}', 'applyForProperty')->name('apply');
        Route::post('/apply-unit/{unitId}', 'applyForUnit')->name('apply.unit');
    });

    // Payments
    Route::controller(TenantBillingController::class)->group(function () {
        Route::get('/payments', 'index')->name('payments');
        Route::get('/payments/{id}', 'show')->name('payments.show');
        Route::post('/payments/{id}/submit-proof', 'submitProof')->name('payments.submit-proof');
    });

    // Maintenance
    Route::controller(TenantMaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/update-notes', 'updateNotes')->name('update-notes');
        Route::post('/{id}/cancel', 'cancel')->name('cancel');
        Route::post('/{id}/comment', 'addComment')->name('add-comment');
        Route::post('/{id}/rate', 'rate')->name('rate');
    });

    // Announcements
    Route::controller(TenantAnnouncementController::class)->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
    });

    // Chat & Messaging
    Route::controller(TenantChatController::class)->group(function () {
        Route::get('/messages', 'index')->name('chat');
        Route::get('/messages/{id}', 'show')->name('chat.show');
        Route::post('/messages/start-with-landlord', 'startWithLandlord')->name('chat.start-with-landlord');
        Route::post('/messages/create-ticket', 'createTicket')->name('chat.create-ticket');
        Route::post('/messages/{id}/send', 'sendMessage')->name('chat.send');
        Route::get('/messages/{id}/fetch', 'getMessages')->name('chat.fetch');
        Route::post('/messages/{id}/read', 'markAsRead')->name('chat.mark-read');
        Route::get('/api/conversations', 'getConversations')->name('chat.conversations');
        Route::get('/api/unread-count', 'getUnreadCount')->name('chat.unread-count');
    });
});

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['role:staff', 'verified'])->prefix('staff')->name('staff.')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::controller(StaffProfileController::class)->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/update-password', 'updatePassword')->name('update-password');
        Route::post('/assignments/{id}/complete', 'completeAssignment')->name('complete-assignment');
    });

    // Maintenance
    Route::controller(MaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', 'staffIndex')->name('index');
        Route::get('/{id}', 'staffShow')->name('show');
        Route::post('/{id}/update-status', 'staffUpdateStatus')->name('update-status');
        Route::post('/{id}/update-notes', 'staffUpdateNotes')->name('update-notes');
        Route::post('/{id}/comment', 'addComment')->name('add-comment');
    });

    // Announcements
    Route::controller(AnnouncementController::class)->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', 'staffIndex')->name('index');
        Route::get('/{id}', 'staffShow')->name('show');
    });

    // Chat & Messaging
    Route::controller(ChatController::class)->group(function () {
        Route::get('/messages', 'staffIndex')->name('chat');
        Route::get('/messages/{id}', 'show')->name('chat.show');
        Route::post('/messages/{id}/send', 'sendMessage')->name('chat.send');
        Route::get('/messages/{id}/fetch', 'getMessages')->name('chat.fetch');
        Route::post('/messages/{id}/read', 'markAsRead')->name('chat.mark-read');
        Route::get('/api/conversations', 'getConversations')->name('chat.conversations');
        Route::get('/api/unread-count', 'getUnreadCount')->name('chat.unread-count');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Unit Management (Super Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(UnitController::class)->prefix('units')->name('units.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/filter', 'filter')->name('filter');
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/types', 'getUnitTypes')->name('types');
    });
});

/*
|--------------------------------------------------------------------------
| Notification Routes (All Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/unread-count', 'unreadCount')->name('unread-count');
    Route::post('/{id}/read', 'markAsRead')->name('mark-read');
    Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
});

/*
|--------------------------------------------------------------------------
| Dashboard Redirect (Role-based)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }

    return match ($user->role) {
        'super_admin' => redirect()->route('super-admin.dashboard'),
        'landlord' => redirect()->route(
            $user->status === 'approved' ? 'landlord.dashboard' :
            ($user->status === 'pending' ? 'landlord.pending' : 'landlord.rejected')
        ),
        'tenant' => redirect()->route('tenant.dashboard'),
        'staff' => redirect()->route('staff.dashboard'),
        default => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| System Routes (Health Check & Debug)
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    try {
        $dbConnected = DB::connection()->getPdo() ? 'connected' : 'disconnected';
    } catch (\Exception $exception) {
        \Illuminate\Support\Facades\Log::error('Health check DB connection failed', ['exception' => $exception]);
        $dbConnected = 'disconnected';
    }

    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => $dbConnected,
        'version' => app()->version(),
        'app_key' => config('app.key') ? 'set' : 'missing',
        'app_debug' => config('app.debug'),
        'app_env' => config('app.env'),
    ]);
});

Route::get('/debug', function () {
    return response()->json([
        'message' => 'Laravel is working!',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => app()->environment(),
        'config_cached' => app()->configurationIsCached(),
        'routes_cached' => app()->routesAreCached(),
        'app_key' => config('app.key') ? 'configured' : 'missing',
        'database' => [
            'default' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'database' => config('database.connections.mysql.database'),
        ],
    ]);
});
