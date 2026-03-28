<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\TenantAssignment;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display announcements for tenant
     */
    public function index()
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeAssignment) {
            return view('tenant.announcements.index', ['announcements' => collect(), 'activeAssignment' => null]);
        }

        $propertyId = $activeAssignment->unit?->property_id;
        $landlordId = $activeAssignment->landlord_id;

        $announcements = Announcement::where('user_id', $landlordId)
            ->active()
            ->where(function ($query) use ($propertyId) {
                $query->whereNull('property_id')
                    ->orWhere('property_id', $propertyId);
            })
            ->whereIn('audience', ['all_tenants', 'property_tenants', 'everyone'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('tenant.announcements.index', compact('announcements', 'activeAssignment'));
    }

    /**
     * Display the specified announcement for tenant
     */
    public function show($id)
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeAssignment) {
            abort(403);
        }

        $announcement = Announcement::active()
            ->where('user_id', $activeAssignment->landlord_id)
            ->whereIn('audience', ['all_tenants', 'property_tenants', 'everyone'])
            ->where(function ($query) use ($activeAssignment) {
                $query->whereNull('property_id')
                    ->orWhere('property_id', $activeAssignment->unit?->property_id);
            })
            ->findOrFail($id);

        return view('tenant.announcements.show', compact('announcement'));
    }
}
