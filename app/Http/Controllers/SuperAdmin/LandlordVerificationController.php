<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LandlordDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandlordVerificationController extends Controller
{
    public function pendingLandlords()
    {
        $pendingLandlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', fn ($query) => $query->where('status', 'pending'))
            ->with(['landlordProfile', 'approvedBy', 'landlordDocuments'])
            ->latest('users.created_at')
            ->paginate(15);

        return view('super-admin.pending-landlords', compact('pendingLandlords'));
    }

    public function approveLandlord($id)
    {
        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return back()->with('error', 'User is not a landlord.');
        }

        if ($landlord->landlordProfile && $landlord->landlordProfile->status === 'approved') {
            return back()->with('error', 'This landlord is already approved.');
        }

        $landlord->approve(Auth::id());
        $landlord->load('landlordProfile');

        return back()->with('success', 'Landlord approved successfully.');
    }

    public function rejectLandlord(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return back()->with('error', 'User is not a landlord.');
        }

        if ($landlord->landlordProfile && $landlord->landlordProfile->status === 'rejected') {
            return back()->with('error', 'This landlord application has already been rejected.');
        }

        $landlord->reject(Auth::id(), $request->reason);

        return back()->with('success', 'Landlord rejected successfully.');
    }

    public function reviewLandlordDocuments($id)
    {
        $landlord = User::where('role', 'landlord')->findOrFail($id);
        $documents = $landlord->landlordDocuments()->latest()->get();

        return view('super-admin.review-landlord-docs', compact('landlord', 'documents'));
    }

    public function verifyLandlordDocument(Request $request, $docId)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $doc = LandlordDocument::findOrFail($docId);
        $doc->update([
            'verification_status' => $request->status,
            'verified_at' => $request->status === 'verified' ? now() : null,
            'verified_by' => Auth::id(),
            'verification_notes' => $request->notes,
        ]);

        return back()->with('success', 'Document updated.');
    }
}
