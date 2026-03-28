<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\TenantAssignment;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    /**
     * List all tenants assigned to landlord's units.
     */
    public function tenants()
    {
        $landlordId = Auth::id();
        $tenants = User::where('role', 'tenant')
            ->whereHas('tenantAssignments', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })->get();

        return view('landlord.tenants', compact('tenants'));
    }

    /**
     * Show tenant assignment history with filters.
     */
    public function tenantHistory(Request $request)
    {
        $landlordId = Auth::id();

        $query = TenantAssignment::where('landlord_id', $landlordId)
            ->with(['tenant', 'unit.property']);

        if ($request->filled('property_id')) {
            $query->whereHas('unit.property', function ($q) use ($request) {
                $q->where('id', $request->property_id);
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('tenant_name')) {
            $searchTerm = $request->tenant_name;
            $query->whereHas('tenant', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('lease_start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('lease_end_date', '<=', $request->date_to);
        }

        $assignments = $query->orderBy('lease_start_date', 'desc')->paginate(20);

        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $properties = $landlord->properties()->orderBy('name')->get();
        $apartments = $properties; // Backward compatibility

        $units = Unit::whereHas('property', function ($q) use ($landlordId) {
            $q->where('landlord_id', $landlordId);
        })->with('property')->orderBy('unit_number')->get();

        $stats = [
            'total_assignments' => TenantAssignment::where('landlord_id', $landlordId)->count(),
            'active_assignments' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'active')->count(),
            'terminated_assignments' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'terminated')->count(),
            'total_revenue' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'active')->sum('rent_amount'),
        ];

        return view('landlord.tenant-history', compact('assignments', 'apartments', 'properties', 'units', 'stats'));
    }

    /**
     * Export tenant history as CSV.
     */
    public function exportTenantHistoryCSV(Request $request)
    {
        $landlordId = Auth::id();

        $query = TenantAssignment::where('landlord_id', $landlordId)
            ->with(['tenant', 'unit.property']);

        if ($request->filled('property_id')) {
            $query->whereHas('unit.property', function ($q) use ($request) {
                $q->where('id', $request->property_id);
            });
        }

        $assignments = $query->orderBy('lease_start_date', 'desc')->get();

        $filename = 'tenant-history-'.date('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($assignments) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Tenant Name', 'Email', 'Phone', 'Property Name', 'Unit Number',
                'Bedrooms', 'Move-in Date', 'Move-out Date', 'Rent Amount', 'Status',
            ]);

            foreach ($assignments as $assignment) {
                fputcsv($file, [
                    $assignment->tenant->name ?? 'N/A',
                    $assignment->tenant->email ?? 'N/A',
                    $assignment->tenant->phone ?? 'N/A',
                    $assignment->unit->property->name ?? 'N/A',
                    $assignment->unit->unit_number ?? 'N/A',
                    $assignment->unit->bedrooms ?? 'N/A',
                    $assignment->lease_start_date ? $assignment->lease_start_date->format('M d, Y') : 'N/A',
                    $assignment->lease_end_date ? $assignment->lease_end_date->format('M d, Y') : 'N/A',
                    '₱'.number_format($assignment->rent_amount, 2),
                    ucfirst($assignment->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
