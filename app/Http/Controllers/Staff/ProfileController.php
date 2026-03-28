<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function profile()
    {
        try {
            $staff = Auth::user();

            if (! $staff) {
                return redirect()->route('login')->with('error', 'Please log in to access your profile.');
            }

            $assignment = StaffAssignment::where('staff_id', $staff->id)
                ->where('status', 'active')
                ->with(['unit.property', 'landlord'])
                ->first();

            return view('staff.profile', compact('staff', 'assignment'));
        } catch (\Exception $e) {
            Log::error('Staff profile error: '.$e->getMessage(), ['user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);

            return redirect()->route('staff.dashboard')->with('error', 'Unable to load profile. Please try again.');
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $staff = Auth::user();

            if (! $staff) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (! Hash::check($request->current_password, $staff->password)) {
                return response()->json(['error' => 'Current password is incorrect.'], 400);
            }

            $staff->update(['password' => Hash::make($request->new_password)]);

            Log::info('Staff password updated', ['staff_id' => $staff->id, 'staff_email' => $staff->email, 'updated_at' => now()]);

            return response()->json(['success' => 'Password updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Staff password update error: '.$e->getMessage(), ['staff_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['error' => 'Failed to update password. Please try again.'], 500);
        }
    }

    public function completeAssignment(Request $request, $id)
    {
        try {
            $staff = Auth::user();

            $assignment = StaffAssignment::where('id', $id)
                ->where('staff_id', $staff->id)
                ->where('status', 'active')
                ->with(['unit.property', 'landlord'])
                ->first();

            if (! $assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found or you do not have permission to complete it.',
                ], 404);
            }

            $assignment->update([
                'status' => 'completed',
                'assignment_end_date' => now(),
            ]);

            Log::info('Staff assignment completed', [
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
                'message' => 'Assignment marked as completed successfully! The landlord has been notified.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to complete staff assignment', [
                'assignment_id' => $id,
                'staff_id' => Auth::id(),
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete assignment. Please try again.',
            ], 500);
        }
    }
}
