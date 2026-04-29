<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LandlordDocument;
use App\Models\User;
use App\Services\LandlordVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LandlordVerificationController extends Controller
{
    public function __construct(
        private LandlordVerificationService $landlordVerification
    ) {}

    public function pendingLandlords()
    {
        $pendingLandlords = $this->landlordVerification->pendingLandlordsPaginated(15);

        return view('super-admin.pending-landlords', compact('pendingLandlords'));
    }

    public function approveLandlord($id)
    {
        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('error', 'User is not a landlord.');
        }

        try {
            $result = $this->landlordVerification->approvePendingLandlord($landlord, (int) Auth::id());
        } catch (\RuntimeException $e) {
            Log::warning('Landlord approve failed', ['landlord_id' => $landlord->id, 'message' => $e->getMessage()]);

            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('error', $e->getMessage());
        }

        Cache::forget('super_admin.pending_landlords_count');

        if ($result === 'already_approved') {
            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('info', 'This landlord is already approved. They have been removed from the pending list.');
        }

        return redirect()
            ->route('super-admin.pending-landlords')
            ->with('success', 'Landlord approved successfully.');
    }

    public function rejectLandlord(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('error', 'User is not a landlord.');
        }

        try {
            $result = $this->landlordVerification->rejectPendingLandlord(
                $landlord,
                (int) Auth::id(),
                $request->input('reason')
            );
        } catch (\RuntimeException $e) {
            Log::warning('Landlord reject failed', ['landlord_id' => $landlord->id, 'message' => $e->getMessage()]);

            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('error', $e->getMessage());
        }

        Cache::forget('super_admin.pending_landlords_count');

        if ($result === 'already_rejected') {
            return redirect()
                ->route('super-admin.pending-landlords')
                ->with('info', 'This application was already rejected.');
        }

        return redirect()
            ->route('super-admin.pending-landlords')
            ->with('success', 'Landlord rejected successfully.');
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
