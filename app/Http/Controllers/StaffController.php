<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Models\StaffAssignment;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    /**
     * Show staff for landlord (both assigned and unassigned)
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'staff_type']);
        $landlordId = Auth::id();
        
        // Get all staff members with their active maintenance tasks
        $staffQuery = User::where('role', 'staff')
            ->with(['staffProfile', 
                   'assignedMaintenanceRequests' => function($query) use ($landlordId) {
                       $query->where('landlord_id', $landlordId)
                             ->whereNotIn('status', ['completed', 'cancelled'])
                             ->with(['unit.apartment'])
                             ->latest();
                   }]);
        
        // Apply filters
        if (isset($filters['staff_type']) && $filters['staff_type']) {
            $staffQuery->whereHas('staffProfile', function($query) use ($filters) {
                $query->where('staff_type', $filters['staff_type']);
            });
        }
        
        if (isset($filters['status']) && $filters['status']) {
            $staffQuery->whereHas('staffProfile', function($query) use ($filters) {
                $query->where('status', $filters['status']);
            });
        }
        
        $staff = $staffQuery->paginate(15);
        $stats = $this->getStaffStats();

        return view('landlord.staff', compact('staff', 'stats', 'filters'));
    }

    /**
     * Show form to assign staff to unit
     */
    public function create($unitId = null)
    {
        $units = Unit::whereHas('apartment', function($query) {
            $query->where('landlord_id', Auth::id());
        })->with('apartment')->get();

        $selectedUnit = null;
        if ($unitId) {
            $selectedUnit = $units->find($unitId);
        }

        return view('landlord.assign-staff', compact('units', 'selectedUnit'));
    }

    /**
     * Add new staff member
     */
    public function addStaff(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'staff_type' => 'required|string|max:100',
            ]);

            // Generate unique email
            $baseEmail = strtolower(str_replace(' ', '.', $request->name)) . '@staff.housesync.com';
            $email = $baseEmail;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = str_replace('@staff.housesync.com', $counter . '@staff.housesync.com', $baseEmail);
                $counter++;
            }

            // Generate password
            $password = Str::random(8);

            // Create staff user
            $staff = User::create([
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'staff',
            ]);

            // Delete auto-created profile if it exists (the boot method creates a generic one)
            if ($staff->staffProfile) {
                $staff->staffProfile->delete();
            }

            // Create staff profile with actual data
            StaffProfile::create([
                'user_id' => $staff->id,
                'created_by_landlord_id' => Auth::id(), // CRITICAL: Track which landlord created this staff
                'name' => $request->name,
                'staff_type' => $request->staff_type,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'active',
            ]);

            return redirect()->route('landlord.staff')
                ->with('success', 'Staff member added successfully!')
                ->with('staff_credentials', [
                    'email' => $email,
                    'password' => $password,
                    'staff_name' => $request->name,
                    'staff_type' => $request->staff_type
                ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add staff member: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Assign staff to unit
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'unit_id' => 'required|exists:units,id',
                'staff_id' => 'required|exists:users,id',
                'staff_type' => 'required|string|max:100',
                'assignment_start_date' => 'required|date|after_or_equal:today',
                'assignment_end_date' => 'nullable|date|after:assignment_start_date',
                'hourly_rate' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Verify landlord owns the unit
            $unit = Unit::whereHas('apartment', function($query) {
                $query->where('landlord_id', Auth::id());
            })->findOrFail($request->unit_id);

            // Verify the selected user is a staff member
            $staff = User::where('id', $request->staff_id)
                ->where('role', 'staff')
                ->where('status', 'active')
                ->firstOrFail();

            // Check if staff is already assigned to this unit
            $existingAssignment = StaffAssignment::where('unit_id', $request->unit_id)
                ->where('staff_id', $request->staff_id)
                ->where('status', 'active')
                ->first();

            if ($existingAssignment) {
                return back()->with('error', 'This staff member is already assigned to this unit.')->withInput();
            }

            // Create staff assignment
            $assignment = StaffAssignment::create([
                'unit_id' => $request->unit_id,
                'staff_id' => $staff->id,
                'landlord_id' => Auth::id(),
                'staff_type' => $request->staff_type,
                'assignment_start_date' => $request->assignment_start_date,
                'assignment_end_date' => $request->assignment_end_date,
                'hourly_rate' => $request->hourly_rate,
                'notes' => $request->notes,
            ]);

            return redirect()->route('landlord.staff')
                ->with('success', "Staff member {$staff->name} has been assigned to unit {$unit->unit_number} successfully!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign staff: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show staff assignment details
     */
    public function show($id)
    {
        $assignment = StaffAssignment::where('landlord_id', Auth::id())
            ->with(['staff', 'unit.apartment'])
            ->findOrFail($id);

        return view('landlord.staff-details', compact('assignment'));
    }

    /**
     * Update staff assignment status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,terminated,completed',
        ]);

        $assignment = StaffAssignment::where('landlord_id', Auth::id())
            ->findOrFail($id);

        $assignment->update(['status' => $request->status]);

        return back()->with('success', 'Staff assignment status updated successfully.');
    }

    /**
     * Delete staff assignment
     */
    public function destroy($id)
    {
        try {
            $assignment = StaffAssignment::where('landlord_id', Auth::id())
                ->with(['staff'])
                ->findOrFail($id);

            // Delete the staff user account
            $assignment->staff->delete();

            // Delete the assignment
            $assignment->delete();

            return redirect()->route('landlord.staff')
                ->with('success', 'Staff assignment deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete staff assignment. Please try again.');
        }
    }

    /**
     * Get staff members by type for assignment
     */
    public function getStaffByType($staffType)
    {
        // Get active staff members that match the selected staff type
        $staff = User::where('role', 'staff')
            ->where('status', 'active')
            ->where('staff_type', $staffType)
            ->select('id', 'name', 'email', 'staff_type')
            ->get();

        return response()->json(['staff' => $staff]);
    }

    /**
     * Get staff credentials
     */
    public function getCredentials($id)
    {
        $assignment = StaffAssignment::where('landlord_id', Auth::id())
            ->with('staff')
            ->findOrFail($id);

        return response()->json([
            'email' => $assignment->staff->email,
            'password' => $assignment->generated_password ?? 'Password not available'
        ]);
    }

    /**
     * Get landlord staff assignments with filters
     */
    private function getLandlordStaffAssignments($landlordId, $filters = [])
    {
        $query = StaffAssignment::where('landlord_id', $landlordId)
            ->with(['staff', 'unit.apartment']);

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['staff_type']) && $filters['staff_type']) {
            $query->where('staff_type', $filters['staff_type']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Get landlord staff statistics
     */
    private function getLandlordStaffStats($landlordId)
    {
        $assignments = StaffAssignment::where('landlord_id', $landlordId);
        
        return [
            'total_assignments' => $assignments->count(),
            'active_assignments' => $assignments->where('status', 'active')->count(),
            'inactive_assignments' => $assignments->where('status', 'inactive')->count(),
            'terminated_assignments' => $assignments->where('status', 'terminated')->count(),
            'total_staff_types' => $assignments->distinct('staff_type')->count(),
        ];
    }
    
    /**
     * Get overall staff statistics
     */
    private function getStaffStats()
    {
        $totalStaff = User::where('role', 'staff')->count();
        $activeStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', function($query) {
                $query->where('status', 'active');
            })->count();
        $inactiveStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', function($query) {
                $query->where('status', 'inactive');
            })->count();
        $staffTypes = StaffProfile::distinct('staff_type')->count('staff_type');
        
        return [
            'total' => $totalStaff,
            'active' => $activeStaff,
            'inactive' => $inactiveStaff,
            'staff_types' => $staffTypes,
        ];
    }

    /**
     * Show staff dashboard
     */
    public function staffDashboard()
    {
        $staff = Auth::user();
        $staffId = $staff->id;
        
        // Get maintenance requests assigned to this staff member
        $activeMaintenanceRequests = \App\Models\MaintenanceRequest::where('assigned_staff_id', $staffId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['unit.apartment', 'tenant.tenantProfile', 'landlord.landlordProfile'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get current active task (highest priority, not completed)
        $currentTask = $activeMaintenanceRequests->first();
        
        // Get stats
        $stats = [
            'total_assigned' => \App\Models\MaintenanceRequest::where('assigned_staff_id', $staffId)->count(),
            'active_tasks' => \App\Models\MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'in_progress' => \App\Models\MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->where('status', 'in_progress')
                ->count(),
            'completed' => \App\Models\MaintenanceRequest::where('assigned_staff_id', $staffId)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('staff.dashboard', compact('activeMaintenanceRequests', 'currentTask', 'stats'));
    }

    /**
     * Get maintenance requests for a unit (legacy method)
     */
    private function getMaintenanceRequests($unitId)
    {
        return \App\Models\MaintenanceRequest::where('unit_id', $unitId)
            ->with(['tenant', 'assignedStaff'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Complete staff assignment
     */
    public function completeAssignment(Request $request, $id)
    {
        try {
            $staff = Auth::user();
            
            // Find the assignment and verify it belongs to the current staff member
            $assignment = StaffAssignment::where('id', $id)
                ->where('staff_id', $staff->id)
                ->where('status', 'active')
                ->with(['unit.apartment', 'landlord'])
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found or you do not have permission to complete it.'
                ], 404);
            }

            // Update the assignment status to completed
            $assignment->update([
                'status' => 'completed',
                'assignment_end_date' => now(),
            ]);

            // Log the completion for audit purposes
            \Illuminate\Support\Facades\Log::info('Staff assignment completed', [
                'assignment_id' => $assignment->id,
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'unit_id' => $assignment->unit_id,
                'unit_number' => $assignment->unit->unit_number,
                'landlord_id' => $assignment->landlord_id,
                'completion_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment marked as completed successfully! The landlord has been notified.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to complete staff assignment', [
                'assignment_id' => $id,
                'staff_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete assignment. Please try again.'
            ], 500);
        }
    }

    /**
     * Show staff profile page
     */
    public function staffProfile()
    {
        try {
            $staff = Auth::user();
            
            if (!$staff) {
                return redirect()->route('login')->with('error', 'Please log in to access your profile.');
            }
            
            // Get staff's active assignment
            $assignment = StaffAssignment::where('staff_id', $staff->id)
                ->where('status', 'active')
                ->with(['unit.apartment', 'landlord'])
                ->first();

            return view('staff.profile', compact('staff', 'assignment'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Staff profile error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('staff.dashboard')->with('error', 'Unable to load profile. Please try again.');
        }
    }

    /**
     * Update staff password (no document verification required)
     */
    public function updatePassword(Request $request)
    {
        try {
            $staff = Auth::user();
            
            if (!$staff) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            // Verify current password
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $staff->password)) {
                return response()->json(['error' => 'Current password is incorrect.'], 400);
            }

            // Update password
            $staff->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
            ]);

            // Log the password change
            \Illuminate\Support\Facades\Log::info('Staff password updated', [
                'staff_id' => $staff->id,
                'staff_email' => $staff->email,
                'updated_at' => now()
            ]);

            return response()->json(['success' => 'Password updated successfully!']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Staff password update error: ' . $e->getMessage(), [
                'staff_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to update password. Please try again.'], 500);
        }
    }
} 