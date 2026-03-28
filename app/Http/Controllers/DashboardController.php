<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated user's role.
     * UI differences are handled by role-specific views.
     */
    public function index(DashboardService $dashboardService)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $result = $dashboardService->getDataFor($user);

        return view($result['view'], $result['data']);
    }
}
