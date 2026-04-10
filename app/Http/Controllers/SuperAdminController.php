<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

/**
 * Some environments still reference this class (e.g. old route caches).
 * Super-admin UI is served via DashboardController + DashboardService; this only delegates.
 */
class SuperAdminController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        return app(DashboardController::class)->index($dashboardService);
    }

    public function dashboard(DashboardService $dashboardService)
    {
        return $this->index($dashboardService);
    }
}
