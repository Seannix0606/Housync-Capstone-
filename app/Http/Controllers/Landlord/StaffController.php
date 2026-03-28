<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\StaffAssignment;
use App\Models\StaffProfile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'staff_type']);
        $landlordId = Auth::id();

        $staffQuery = User::where('role', 'staff')
            ->whereHas('staffProfile', fn ($q) => $q->where('created_by_landlord_id', $landlordId))
            ->with(['staffProfile',
                'assignedMaintenanceRequests' => function ($query) use ($landlordId) {
                    $query->where('landlord_id', $landlordId)
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->with(['unit.property'])
                        ->latest();
                }]);

        if (isset($filters['staff_type']) && $filters['staff_type']) {
            $staffQuery->whereHas('staffProfile', fn ($query) => $query->where('staff_type', $filters['staff_type']));
        }

        if (isset($filters['status']) && $filters['status']) {
            $staffQuery->whereHas('staffProfile', fn ($query) => $query->where('status', $filters['status']));
        }

        $staff = $staffQuery->paginate(15);
        $stats = $this->getStaffStats();

        return view('landlord.staff', compact('staff', 'stats', 'filters'));
    }

    public function create($unitId = null)
    {
        $units = Unit::whereHas('property', fn ($query) => $query->where('landlord_id', Auth::id()))
            ->with('property')
            ->get();

        $selectedUnit = $unitId ? $units->find($unitId) : null;

        return view('landlord.assign-staff', compact('units', 'selectedUnit'));
    }

    public function addStaff(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'staff_type' => 'required|string|max:100',
            ]);

            $baseEmail = strtolower(str_replace(' ', '.', $request->name)).'@staff.housesync.com';
            $email = $baseEmail;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = str_replace('@staff.housesync.com', $counter.'@staff.housesync.com', $baseEmail);
                $counter++;
            }

            $password = Str::random(8);

            $staff = User::create([
                'email' => $email,
                'password' => $password,
                'role' => 'staff',
            ]);

            if ($staff->staffProfile) {
                $staff->staffProfile->delete();
            }

            StaffProfile::create([
                'user_id' => $staff->id,
                'created_by_landlord_id' => Auth::id(),
                'name' => $request->name,
                'staff_type' => $request->staff_type,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'active',
            ]);

            event(new Registered($staff));

            return redirect()->route('landlord.staff')
                ->with('success', 'Staff member added successfully!')
                ->with('staff_credentials', [
                    'email' => $email,
                    'password' => $password,
                    'staff_name' => $request->name,
                    'staff_type' => $request->staff_type,
                ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to add staff member', ['exception' => $e]);

            return back()->with('error', 'Failed to add staff member. Please try again.')->withInput();
        }
    }

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

            $unit = Unit::whereHas('property', fn ($query) => $query->where('landlord_id', Auth::id()))
                ->findOrFail($request->unit_id);

            $staff = User::where('id', $request->staff_id)
                ->where('role', 'staff')
                ->whereHas('staffProfile', fn ($q) => $q->where('created_by_landlord_id', Auth::id())->where('status', 'active'))
                ->firstOrFail();

            $existingAssignment = StaffAssignment::where('unit_id', $request->unit_id)
                ->where('staff_id', $request->staff_id)
                ->where('status', 'active')
                ->first();

            if ($existingAssignment) {
                return back()->with('error', 'This staff member is already assigned to this unit.')->withInput();
            }

            StaffAssignment::create([
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
            Log::error('Failed to assign staff', ['exception' => $e]);

            return back()->with('error', 'Failed to assign staff. Please try again.')->withInput();
        }
    }

    public function show($id)
    {
        $assignment = StaffAssignment::where('landlord_id', Auth::id())
            ->with(['staff', 'unit.property'])
            ->findOrFail($id);

        return view('landlord.staff-details', compact('assignment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,inactive,terminated,completed']);

        $assignment = StaffAssignment::where('landlord_id', Auth::id())->findOrFail($id);
        $assignment->update(['status' => $request->status]);

        return back()->with('success', 'Staff assignment status updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $assignment = StaffAssignment::where('landlord_id', Auth::id())
                ->findOrFail($id);

            $assignment->update(['status' => 'terminated']);

            return redirect()->route('landlord.staff')->with('success', 'Staff assignment terminated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to terminate staff assignment. Please try again.');
        }
    }

    public function getStaffByType($staffType)
    {
        $landlordId = Auth::id();

        $staff = User::where('role', 'staff')
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($q) use ($staffType, $landlordId) {
                $q->where('staff_type', $staffType)
                    ->where('created_by_landlord_id', $landlordId);
            })
            ->with('staffProfile')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'staff_type' => $u->staff_type]);

        return response()->json(['staff' => $staff]);
    }

    public function getCredentials($id)
    {
        $assignment = StaffAssignment::where('landlord_id', Auth::id())
            ->with('staff')
            ->findOrFail($id);

        return response()->json([
            'email' => $assignment->staff->email,
            'note' => 'Passwords are not stored in plain text. Use the password reset flow to send a new password to this staff member.',
        ]);
    }

    private function getStaffStats(): array
    {
        $landlordId = Auth::id();

        $totalStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', fn ($q) => $q->where('created_by_landlord_id', $landlordId))
            ->count();
        $activeStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', fn ($q) => $q->where('created_by_landlord_id', $landlordId)->where('status', 'active'))
            ->count();
        $inactiveStaff = User::where('role', 'staff')
            ->whereHas('staffProfile', fn ($q) => $q->where('created_by_landlord_id', $landlordId)->where('status', 'inactive'))
            ->count();
        $staffTypes = StaffProfile::where('created_by_landlord_id', $landlordId)
            ->distinct('staff_type')
            ->count('staff_type');

        return [
            'total' => $totalStaff,
            'active' => $activeStaff,
            'inactive' => $inactiveStaff,
            'staff_types' => $staffTypes,
        ];
    }
}
