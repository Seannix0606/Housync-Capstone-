<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaseController extends Controller
{
    /**
     * Show tenant lease page
     */
    public function lease()
    {
        try {
            /** @var \App\Models\User $tenant */
            $tenant = Auth::user();

            if (! $tenant) {
                return redirect()->route('login')->with('error', 'Please log in to access your lease information.');
            }

            $assignment = $tenant->tenantAssignments()
                ->with([
                    'unit.property.landlord',
                    'documents',
                    'landlord',
                ])
                ->where('status', 'active')
                ->first();

            return view('tenant-lease', compact('tenant', 'assignment'));

        } catch (\Exception $exception) {
            Log::error('Tenant lease error', [
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('tenant.dashboard')->with('error', 'Unable to load lease information. Please try again.');
        }
    }
}
