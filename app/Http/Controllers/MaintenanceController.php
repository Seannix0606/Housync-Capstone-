<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceComment;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Unit;
use App\Notifications\NewMaintenanceRequest;
use App\Notifications\MaintenanceStatusUpdated;
use App\Notifications\StaffAssignedToMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    // ==================== LANDLORD METHODS ====================
    
    /**
     * Display a listing of maintenance requests for landlord
     */
    public function index(Request $request)
    {
        $landlordId = Auth::id();
        
        // Base query
        $query = MaintenanceRequest::with(['unit.apartment', 'tenant.tenantProfile', 'assignedStaff.staffProfile'])
            ->where('landlord_id', $landlordId);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by priority
        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }
        
        // Filter by category
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        // Search by title or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get maintenance requests with pagination
        $maintenanceRequests = $query->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => MaintenanceRequest::where('landlord_id', $landlordId)->count(),
            'pending' => MaintenanceRequest::where('landlord_id', $landlordId)->where('status', 'pending')->count(),
            'assigned' => MaintenanceRequest::where('landlord_id', $landlordId)->where('status', 'assigned')->count(),
            'in_progress' => MaintenanceRequest::where('landlord_id', $landlordId)->where('status', 'in_progress')->count(),
            'completed' => MaintenanceRequest::where('landlord_id', $landlordId)->where('status', 'completed')->count(),
            'urgent' => MaintenanceRequest::where('landlord_id', $landlordId)->where('priority', 'urgent')->count(),
        ];
        
        return view('landlord.maintenance.index', compact('maintenanceRequests', 'stats'));
    }
    
    /**
     * Show form for landlord to create a maintenance request
     */
    public function create()
    {
        $landlordId = Auth::id();
        
        // Get landlord's units
        $units = Unit::whereHas('apartment', function($query) use ($landlordId) {
            $query->where('landlord_id', $landlordId);
        })
        ->with(['apartment', 'currentTenant.tenantProfile'])
        ->orderBy('unit_number')
        ->get();
        
        if ($units->isEmpty()) {
            return redirect()
                ->route('landlord.maintenance')
                ->with('error', 'You need to create units before creating maintenance requests.');
        }
        
        // Get available staff for this landlord
        $availableStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', function($query) use ($landlordId) {
                $query->where('status', 'active')
                      ->where('created_by_landlord_id', $landlordId);
            })
            ->with('staffProfile')
            ->get();
        
        return view('landlord.maintenance.create', compact('units', 'availableStaff'));
    }
    
    /**
     * Store a landlord-created maintenance request
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,structural,cleaning,other',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'expected_completion_date' => 'nullable|date|after_or_equal:today',
            'staff_notes' => 'nullable|string|max:1000',
        ]);
        
        $landlordId = Auth::id();
        
        // Verify the unit belongs to this landlord
        $unit = Unit::whereHas('apartment', function($query) use ($landlordId) {
            $query->where('landlord_id', $landlordId);
        })->findOrFail($request->unit_id);
        
        // If staff is being assigned, verify they belong to this landlord
        if ($request->assigned_staff_id) {
            $staffMember = User::where('id', $request->assigned_staff_id)
                ->where('role', 'staff')
                ->whereHas('staffProfile', function($query) use ($landlordId) {
                    $query->where('created_by_landlord_id', $landlordId);
                })
                ->first();
            
            if (!$staffMember) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Invalid staff selection. You can only assign your own staff members.');
            }
        }
        
        // Determine status based on whether staff is assigned
        $status = $request->assigned_staff_id ? 'assigned' : 'pending';
        
        // Create the maintenance request
        $maintenanceRequest = MaintenanceRequest::create([
            'unit_id' => $request->unit_id,
            'tenant_id' => null,
            'landlord_id' => $landlordId,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'category' => $request->category,
            'status' => $status,
            'requested_date' => now(),
            'assigned_staff_id' => $request->assigned_staff_id,
            'expected_completion_date' => $request->expected_completion_date,
            'staff_notes' => $request->staff_notes,
        ]);

        ActivityLog::log('maintenance_created', "Created maintenance request: {$maintenanceRequest->title}", $maintenanceRequest);

        if ($request->assigned_staff_id) {
            $staff = User::find($request->assigned_staff_id);
            if ($staff) {
                $staff->notify(new StaffAssignedToMaintenance($maintenanceRequest));
            }
        }
        
        return redirect()
            ->route('landlord.maintenance.show', $maintenanceRequest->id)
            ->with('success', 'Maintenance request created successfully!');
    }
    
    /**
     * Display the specified maintenance request
     */
    public function show($id)
    {
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::with([
            'unit.apartment', 
            'tenant.tenantProfile', 
            'assignedStaff.staffProfile',
            'landlord',
            'comments.user',
        ])
        ->where('landlord_id', $landlordId)
        ->findOrFail($id);
        
        $availableStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', function($query) use ($landlordId) {
                $query->where('status', 'active')
                      ->where('created_by_landlord_id', $landlordId);
            })
            ->with('staffProfile')
            ->get();
        
        return view('landlord.maintenance.show', compact('maintenanceRequest', 'availableStaff'));
    }
    
    /**
     * Assign staff to a maintenance request
     */
    public function assignStaff(Request $request, $id)
    {
        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'expected_completion_date' => 'nullable|date|after_or_equal:today',
        ]);
        
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('landlord_id', $landlordId)
            ->findOrFail($id);
        
        // SECURITY VALIDATION: Verify the staff member belongs to this landlord
        $staffMember = User::where('id', $request->staff_id)
            ->where('role', 'staff')
            ->whereHas('staffProfile', function($query) use ($landlordId) {
                $query->where('created_by_landlord_id', $landlordId);
            })
            ->first();
        
        if (!$staffMember) {
            return redirect()
                ->route('landlord.maintenance.show', $id)
                ->with('error', 'Invalid staff selection. You can only assign your own staff members.');
        }
        
        $updateData = [
            'assigned_staff_id' => $request->staff_id,
            'status' => 'assigned',
        ];
        
        if ($request->expected_completion_date) {
            $updateData['expected_completion_date'] = $request->expected_completion_date;
        }
        
        $maintenanceRequest->update($updateData);

        // Notify the assigned staff
        $staffMember->notify(new StaffAssignedToMaintenance($maintenanceRequest));

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $landlordId,
            'comment' => "Staff assigned: {$staffMember->name}",
            'type' => 'staff_assigned',
            'metadata' => ['staff_id' => $staffMember->id, 'staff_name' => $staffMember->name],
        ]);

        ActivityLog::log('staff_assigned', "Assigned {$staffMember->name} to maintenance request", $maintenanceRequest);
        
        return redirect()
            ->route('landlord.maintenance.show', $id)
            ->with('success', 'Staff assigned successfully!');
    }
    
    /**
     * Update the status of a maintenance request
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
        ]);
        
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('landlord_id', $landlordId)
            ->findOrFail($id);
        
        $oldStatus = $maintenanceRequest->status;
        $updateData = ['status' => $request->status];
        
        if ($request->status === 'completed') {
            $updateData['completed_date'] = now();
        }
        
        $maintenanceRequest->update($updateData);

        // Notify tenant about status change
        if ($maintenanceRequest->tenant_id) {
            $tenant = User::find($maintenanceRequest->tenant_id);
            if ($tenant) {
                $tenant->notify(new MaintenanceStatusUpdated($maintenanceRequest, $oldStatus, $request->status));
            }
        }

        // Notify assigned staff about status change
        if ($maintenanceRequest->assigned_staff_id) {
            $staff = User::find($maintenanceRequest->assigned_staff_id);
            if ($staff) {
                $staff->notify(new MaintenanceStatusUpdated($maintenanceRequest, $oldStatus, $request->status));
            }
        }

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $landlordId,
            'comment' => "Status changed from {$oldStatus} to {$request->status}",
            'type' => 'status_change',
            'metadata' => ['old_status' => $oldStatus, 'new_status' => $request->status],
        ]);

        ActivityLog::log('maintenance_status_updated', "Maintenance status changed: {$oldStatus} → {$request->status}", $maintenanceRequest);
        
        return redirect()
            ->route('landlord.maintenance.show', $id)
            ->with('success', 'Status updated successfully!');
    }
    
    /**
     * Update staff notes
     */
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'staff_notes' => 'required|string',
        ]);
        
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('landlord_id', $landlordId)
            ->findOrFail($id);
        
        $maintenanceRequest->update([
            'staff_notes' => $request->staff_notes,
        ]);
        
        return redirect()
            ->route('landlord.maintenance.show', $id)
            ->with('success', 'Notes updated successfully!');
    }
    
    /**
     * Cancel a maintenance request
     */
    public function cancel($id)
    {
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('landlord_id', $landlordId)
            ->findOrFail($id);
        
        $maintenanceRequest->update([
            'status' => 'cancelled',
        ]);
        
        return redirect()
            ->route('landlord.maintenance')
            ->with('success', 'Maintenance request cancelled successfully!');
    }
    
    /**
     * Delete a maintenance request
     */
    public function destroy($id)
    {
        $landlordId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('landlord_id', $landlordId)
            ->findOrFail($id);
        
        $maintenanceRequest->delete();
        
        return redirect()
            ->route('landlord.maintenance')
            ->with('success', 'Maintenance request deleted successfully!');
    }
    
    // ==================== TENANT METHODS ====================
    
    /**
     * Display maintenance requests for tenant
     */
    public function tenantIndex(Request $request)
    {
        $tenantId = Auth::id();
        
        // Get tenant's active assignment to find landlord
        $activeAssignment = \App\Models\TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();
        
        if (!$activeAssignment) {
            return view('tenant.maintenance.no-assignment');
        }
        
        // Base query
        $query = MaintenanceRequest::with(['unit.apartment', 'assignedStaff.staffProfile', 'landlord'])
            ->where('tenant_id', $tenantId);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get maintenance requests with pagination
        $maintenanceRequests = $query->paginate(10);
        
        // Get statistics
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
    public function tenantCreate()
    {
        $tenantId = Auth::id();
        
        // Get tenant's active assignment
        $activeAssignment = \App\Models\TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['unit.apartment', 'landlord'])
            ->first();
        
        if (!$activeAssignment) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'You need an active unit assignment to create a maintenance request.');
        }
        
        return view('tenant.maintenance.create', compact('activeAssignment'));
    }
    
    /**
     * Store a newly created maintenance request
     */
    public function tenantStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,structural,cleaning,other',
            'tenant_notes' => 'nullable|string',
        ]);
        
        $tenantId = Auth::id();
        
        // Get tenant's active assignment
        $activeAssignment = \App\Models\TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();
        
        if (!$activeAssignment) {
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

        // Notify landlord
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
    public function tenantShow($id)
    {
        $tenantId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::with([
            'unit.apartment', 
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
    public function tenantUpdateNotes(Request $request, $id)
    {
        $request->validate([
            'tenant_notes' => 'required|string',
        ]);
        
        $tenantId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('tenant_id', $tenantId)
            ->findOrFail($id);
        
        // Only allow updates if request is not completed or cancelled
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
    public function tenantCancel($id)
    {
        $tenantId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('tenant_id', $tenantId)
            ->findOrFail($id);
        
        // Only allow cancellation if not yet completed
        if ($maintenanceRequest->status === 'completed') {
            return redirect()
                ->route('tenant.maintenance')
                ->with('error', 'Cannot cancel a completed request.');
        }
        
        $maintenanceRequest->update([
            'status' => 'cancelled',
        ]);
        
        return redirect()
            ->route('tenant.maintenance')
            ->with('success', 'Maintenance request cancelled successfully!');
    }
    
    // ==================== STAFF METHODS ====================
    
    /**
     * Display maintenance requests assigned to staff
     */
    public function staffIndex(Request $request)
    {
        $staffId = Auth::id();
        
        // Base query - get requests assigned to this staff
        $query = MaintenanceRequest::with(['unit.apartment', 'tenant.tenantProfile', 'landlord'])
            ->where('assigned_staff_id', $staffId);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by priority
        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get maintenance requests with pagination
        $maintenanceRequests = $query->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => MaintenanceRequest::where('assigned_staff_id', $staffId)->count(),
            'pending' => MaintenanceRequest::where('assigned_staff_id', $staffId)->whereIn('status', ['pending', 'assigned'])->count(),
            'in_progress' => MaintenanceRequest::where('assigned_staff_id', $staffId)->where('status', 'in_progress')->count(),
            'completed' => MaintenanceRequest::where('assigned_staff_id', $staffId)->where('status', 'completed')->count(),
        ];
        
        return view('staff.maintenance.index', compact('maintenanceRequests', 'stats'));
    }
    
    /**
     * Display specific maintenance request details for staff
     */
    public function staffShow($id)
    {
        $staffId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::with([
            'unit.apartment', 
            'tenant.tenantProfile', 
            'landlord.landlordProfile',
            'assignedStaff.staffProfile',
            'comments.user',
        ])
        ->where('assigned_staff_id', $staffId)
        ->findOrFail($id);
        
        return view('staff.maintenance.show', compact('maintenanceRequest'));
    }
    
    /**
     * Update maintenance request status by staff
     */
    public function staffUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed',
        ]);
        
        $staffId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('assigned_staff_id', $staffId)
            ->findOrFail($id);
        
        $oldStatus = $maintenanceRequest->status;
        $updateData = ['status' => $request->status];
        
        if ($request->status === 'completed') {
            $updateData['completed_date'] = now();
        }
        
        $maintenanceRequest->update($updateData);

        // Notify landlord and tenant about status change
        $landlord = User::find($maintenanceRequest->landlord_id);
        if ($landlord) {
            $landlord->notify(new MaintenanceStatusUpdated($maintenanceRequest, $oldStatus, $request->status));
        }

        if ($maintenanceRequest->tenant_id) {
            $tenant = User::find($maintenanceRequest->tenant_id);
            if ($tenant) {
                $tenant->notify(new MaintenanceStatusUpdated($maintenanceRequest, $oldStatus, $request->status));
            }
        }

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $staffId,
            'comment' => "Status changed from {$oldStatus} to {$request->status}",
            'type' => 'status_change',
            'metadata' => ['old_status' => $oldStatus, 'new_status' => $request->status],
        ]);

        ActivityLog::log('maintenance_status_updated', "Staff updated maintenance status: {$oldStatus} → {$request->status}", $maintenanceRequest);
        
        return redirect()
            ->route('staff.maintenance.show', $id)
            ->with('success', 'Status updated successfully!');
    }
    
    /**
     * Update staff notes on maintenance request
     */
    public function staffUpdateNotes(Request $request, $id)
    {
        $request->validate([
            'staff_notes' => 'required|string',
        ]);
        
        $staffId = Auth::id();
        
        $maintenanceRequest = MaintenanceRequest::where('assigned_staff_id', $staffId)
            ->findOrFail($id);
        
        if (in_array($maintenanceRequest->status, ['completed', 'cancelled'])) {
            return redirect()
                ->route('staff.maintenance.show', $id)
                ->with('error', 'Cannot update notes on a completed or cancelled request.');
        }
        
        $maintenanceRequest->update([
            'staff_notes' => $request->staff_notes,
        ]);
        
        return redirect()
            ->route('staff.maintenance.show', $id)
            ->with('success', 'Notes updated successfully!');
    }

    // ==================== COMMENT METHODS ====================

    /**
     * Add a comment to a maintenance request (available to all roles).
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        // Verify the user has access to this request
        $hasAccess = false;
        if ($user->isLandlord() && $maintenanceRequest->landlord_id === $user->id) {
            $hasAccess = true;
        } elseif ($user->isTenant() && $maintenanceRequest->tenant_id === $user->id) {
            $hasAccess = true;
        } elseif ($user->isStaff() && $maintenanceRequest->assigned_staff_id === $user->id) {
            $hasAccess = true;
        } elseif ($user->isSuperAdmin()) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403);
        }

        MaintenanceComment::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'type' => 'comment',
        ]);

        $redirectRoute = match ($user->role) {
            'landlord' => 'landlord.maintenance.show',
            'tenant' => 'tenant.maintenance.show',
            'staff' => 'staff.maintenance.show',
            default => 'landlord.maintenance.show',
        };

        return redirect()
            ->route($redirectRoute, $id)
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Tenant rates a completed maintenance request.
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
            'comment' => "Rated {$request->rating}/5" . ($request->rating_feedback ? ": {$request->rating_feedback}" : ''),
            'type' => 'comment',
            'metadata' => ['rating' => $request->rating],
        ]);

        return redirect()
            ->route('tenant.maintenance.show', $id)
            ->with('success', 'Thank you for your feedback!');
    }
}

