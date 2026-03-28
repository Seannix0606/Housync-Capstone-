<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\LandlordProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show landlord profile and account settings.
     */
    public function settings()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $profile = $user->landlordProfile;

        return view('landlord.settings', compact('user', 'profile'));
    }

    /**
     * Update landlord profile information.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
            'business_info' => 'nullable|string|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $profile = $user->landlordProfile;

        if (! $profile) {
            $profile = LandlordProfile::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
                'business_info' => $request->business_info,
                'status' => $user->status === 'approved' ? 'approved' : 'pending',
            ]);
        } else {
            $profile->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
                'business_info' => $request->business_info,
            ]);
        }

        Log::info('Landlord profile updated', ['landlord_id' => $user->id]);

        return redirect()->route('landlord.settings')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update landlord password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => $request->password,
        ]);

        Log::info('Landlord password updated', ['landlord_id' => $user->id]);

        return redirect()->route('landlord.settings')->with('success', 'Password changed successfully.');
    }
}
