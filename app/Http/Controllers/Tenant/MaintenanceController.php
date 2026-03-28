<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Notifications\NewMaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    /**
     * Display maintenance requests for tenant
     */
    public function index(Request $request)
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (! $activeAssignment) {
            return view('tenant.maintenance.no-assignment');
        }

        $query = MaintenanceRequest::with(['unit.property', 'assignedStaff.staffProfile', 'landlord'])
            ->where('tenant_id', $tenantId);

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $maintenanceRequests = $query->paginate(10);

        $stats = [
            'total' => MaintenanceRequest::where('tenant_id', $tenantId)->count(),
            'pending' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'in_progress' => MaintenanceRequest::where('tenant_id', $tenantId)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
        ];

        return view('tenant.maintenance.index', compact('maintenanceRequests', 'stats', 'activeAssignment'));
    }

    /**
     * Show form for creating a new maintenance request
     */
    public function create()
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['unit.property', 'landlord'])
            ->first();

        if (! $activeAssignment) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'You need an active unit assignment to create a maintenance request.');
        }

        return view('tenant.maintenance.create', compact('activeAssignment'));
    }

    /**
     * Store a newly created maintenance request
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,structural,cleaning,other',
            'tenant_notes' => 'nullable|string',
        ]);

        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (! $activeAssignment) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'You need an active unit assignment to create a maintenance request.');
        }

        $maintenanceRequest = MaintenanceRequest::create([
            'unit_id' => $activeAssignment->unit_id,
            'tenant_id' => $tenantId,
            'landlord_id' => $activeAssignment->landlord_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'category' => $request->category,
            'status' => 'pending',
            'requested_date' => now(),
            'tenant_notes' => $request->tenant_notes,
        ]);

        $landlord = User::find($activeAssignment->landlord_id);
        if ($landlord) {
            $landlord->notify(new NewMaintenanceRequest($maintenanceRequest));
        }

        ActivityLog::log('maintenance_created', "Tenant submitted maintenance request: {$maintenanceRequest->title}", $maintenanceRequest);

        return redirect()
            ->route('tenant.maintenance.index')
            ->with('success', 'Maintenance request submitted successfully! Your landlord will be notified.');
    }

    /**
     * Display the specified maintenance request for tenant
     */
    public function show($id)
    {
        $tenantId = Auth::id();

        $maintenanceRequest = MaintenanceRequest::with([
            'unit.property',
            'landlord',
            'assignedStaff.staffProfile',
            'comments.user',
        ])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return view('tenant.maintenance.show', compact('maintenanceRequest'));
    }

    /**
     * Update tenant notes on a maintenance request
     */
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'tenant_notes' => 'required|string',
        ]);

        $tenantId = Auth::id();

        $maintenanceRequest = MaintenanceRequest::where('tenant_id', $tenantId)
            ->findOrFail($id);

        if (in_array($maintenanceRequest->status, ['completed', 'cancelled'])) {
            return redirect()
                ->route('tenant.maintenance.show', $id)
                ->with('error', 'Cannot update notes on a completed or cancelled request.');
        }

        $maintenanceRequest->update([
            'tenant_notes' => $request->tenant_notes,
        ]);

        return redirect()
            ->route('tenant.maintenance.show', $id)
            ->with('success', 'Notes updated successfully!');
    }

    /**
     * Cancel a maintenance request (tenant side)
     */
    public function cancel($id)
    {
        $tenantId = Auth::id();

        $maintenanceRequest = MaintenanceRequest::where('tenant_id', $tenantId)
            ->findOrFail($id);

        if ($maintenanceRequest->status === 'completed') {
            return redirect()
                ->route('tenant.maintenance.index')
                ->with('error', 'Cannot cancel a completed request.');
        }

        $maintenanceRequest->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('tenant.maintenance.index')
            ->with('success', 'Maintenance request cancelled successfully!');
    }

    /**
     * Add a comment to a maintenance request
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if ($maintenanceRequest->tenant_id !== $user->id) {
            abort(403);
        }

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'type' => 'comment',
        ]);

        return redirect()
            ->route('tenant.maintenance.show', $id)
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Tenant rates a completed maintenance request
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'rating_feedback' => 'nullable|string|max:1000',
        ]);

        $tenantId = Auth::id();

        $maintenanceRequest = MaintenanceRequest::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereNull('rating')
            ->findOrFail($id);

        $maintenanceRequest->update([
            'rating' => $request->rating,
            'rating_feedback' => $request->rating_feedback,
        ]);

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $tenantId,
            'comment' => "Rated {$request->rating}/5".($request->rating_feedback ? ": {$request->rating_feedback}" : ''),
            'type' => 'comment',
            'metadata' => ['rating' => $request->rating],
        ]);

        return redirect()
            ->route('tenant.maintenance.show', $id)
            ->with('success', 'Thank you for your feedback!');
    }
}
