<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;

class DashboardService
{
    /**
     * Get dashboard data and view for the given user's role.
     *
     * @return array{view: string, data: array}
     */
    public function getDataFor(User $user): array
    {
        return match ($user->role) {
            'landlord' => $this->getLandlordData($user),
            'super_admin' => $this->getSuperAdminData(),
            'tenant' => $this->getTenantData($user),
            'staff' => $this->getStaffData($user),
            default => ['view' => 'auth.login', 'data' => []],
        };
    }

    private function getLandlordData(User $landlord): array
    {
        $landlord->load('landlordProfile');

        $stats = [
            'total_properties' => $landlord->properties()->count(),
            'total_units' => Unit::whereHas('property', fn ($q) => $q->where('landlord_id', $landlord->id))->count(),
            'occupied_units' => Unit::whereHas('property', fn ($q) => $q->where('landlord_id', $landlord->id))
                ->where('status', 'occupied')->count(),
            'available_units' => Unit::whereHas('property', fn ($q) => $q->where('landlord_id', $landlord->id))
                ->where('status', 'available')->count(),
            'total_revenue' => Unit::whereHas('property', fn ($q) => $q->where('landlord_id', $landlord->id))
                ->where('status', 'occupied')->sum('rent_amount'),
        ];

        $properties = $landlord->properties()->with('units')->latest()->take(5)->get();
        $recentUnits = Unit::whereHas('property', fn ($q) => $q->where('landlord_id', $landlord->id))
            ->with('property')
            ->latest()
            ->take(10)
            ->get();

        return [
            'view' => 'landlord.dashboard',
            'data' => [
                'stats' => $stats,
                'properties' => $properties,
                'apartments' => $properties,
                'recentUnits' => $recentUnits,
            ],
        ];
    }

    private function getSuperAdminData(): array
    {
        $stats = [
            'total_users' => User::count(),
            'pending_landlords' => User::pendingLandlords()->count(),
            'approved_landlords' => User::approvedLandlords()->count(),
            'total_tenants' => User::byRole('tenant')->count(),
            'total_properties' => Property::count(),
        ];

        $pendingLandlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', fn ($q) => $q->where('status', 'pending'))
            ->with('landlordProfile')
            ->latest('users.created_at')
            ->take(5)
            ->get()
            ->filter(fn ($l) => $l->landlordProfile && $l->landlordProfile->status === 'pending')
            ->unique('id')
            ->values();

        $recentUsers = User::with('landlordProfile')->latest()->take(10)->get();

        return [
            'view' => 'super-admin.dashboard',
            'data' => [
                'stats' => $stats,
                'pendingLandlords' => $pendingLandlords,
                'recentUsers' => $recentUsers,
            ],
        ];
    }

    private function getTenantData(User $tenant): array
    {
        $assignments = $tenant->tenantAssignments()
            ->with(['unit.property', 'tenant.documents'])
            ->orderByRaw("FIELD(status, 'active', 'pending_approval', 'terminated')")
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assignments->isEmpty()) {
            return [
                'view' => 'tenant.no-assignment',
                'data' => [],
            ];
        }

        return [
            'view' => 'tenant.dashboard',
            'data' => ['assignments' => $assignments],
        ];
    }

    private function getStaffData(User $staff): array
    {
        $staffId = $staff->id;

        $activeMaintenanceRequests = MaintenanceRequest::where('assigned_staff_id', $staffId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['unit.property', 'tenant.tenantProfile', 'landlord.landlordProfile'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_assigned' => MaintenanceRequest::where('assigned_staff_id', $staffId)->count(),
            'active_tasks' => MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'in_progress' => MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->where('status', 'in_progress')
                ->count(),
            'completed' => MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->where('status', 'completed')
                ->count(),
        ];

        return [
            'view' => 'staff.dashboard',
            'data' => [
                'activeMaintenanceRequests' => $activeMaintenanceRequests,
                'currentTask' => $activeMaintenanceRequests->first(),
                'stats' => $stats,
            ],
        ];
    }
}
