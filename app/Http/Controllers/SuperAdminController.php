<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\SuperAdminProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_landlords' => User::pendingLandlords()->count(),
            'approved_landlords' => User::approvedLandlords()->count(),
            'total_tenants' => User::byRole('tenant')->count(),
            'total_properties' => Property::count(),
        ];

        // Use whereHas to avoid duplicates from JOIN
        $pendingLandlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', function ($query) {
                $query->where('status', 'pending');
            })
            ->with('landlordProfile')
            ->latest('users.created_at')
            ->take(5)
            ->get()
            ->filter(function ($landlord) {
                // Double-check that status is actually pending
                return $landlord->landlordProfile && $landlord->landlordProfile->status === 'pending';
            })
            ->unique('id')
            ->values();
        $recentUsers = User::with('landlordProfile')
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.dashboard', compact('stats', 'pendingLandlords', 'recentUsers'));
    }

    public function users()
    {
        $query = User::with('approvedBy');

        // Search by name or email
        if (request('search')) {
            $search = request('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // Filter by role
        if (request('role')) {
            $query->where('role', request('role'));
        }

        // Filter by status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        $users = $query->latest()->paginate(15);

        return view('super-admin.users', compact('users'));
    }

    public function pendingLandlords()
    {
        // Get only landlords with 'pending' status in their profile
        // Use whereHas to avoid duplicates from JOIN
        $pendingLandlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['landlordProfile', 'approvedBy', 'landlordDocuments'])
            ->latest('users.created_at')
            ->get()
            ->filter(function ($landlord) {
                // Double-check that status is actually pending
                return $landlord->landlordProfile && $landlord->landlordProfile->status === 'pending';
            })
            ->unique('id')
            ->values();

        // Manually paginate the filtered collection
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $currentItems = $pendingLandlords->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $pendingLandlords = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $pendingLandlords->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('super-admin.pending-landlords', compact('pendingLandlords'));
    }

    public function approveLandlord($id)
    {
        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return back()->with('error', 'User is not a landlord.');
        }

        // Check if already approved
        if ($landlord->landlordProfile && $landlord->landlordProfile->status === 'approved') {
            return back()->with('error', 'This landlord is already approved.');
        }

        $landlord->approve(Auth::id());

        // Refresh the relationship to ensure status is updated
        $landlord->load('landlordProfile');

        return back()->with('success', 'Landlord approved successfully.');
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
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'verification_notes' => $request->notes,
        ]);

        return back()->with('success', 'Document updated.');
    }

    public function rejectLandlord(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $landlord = User::findOrFail($id);

        if ($landlord->role !== 'landlord') {
            return back()->with('error', 'User is not a landlord.');
        }

        $landlord->reject(Auth::id(), $request->reason);

        return back()->with('success', 'Landlord rejected successfully.');
    }

    public function createUser()
    {
        return view('super-admin.create-user');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,landlord,tenant',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'business_info' => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create role-specific profile
        switch ($request->role) {
            case 'landlord':
                LandlordProfile::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'business_info' => $request->business_info,
                    'status' => 'pending',
                ]);
                if ($request->approve_immediately) {
                    $user->approve(Auth::id());
                }
                break;
            case 'tenant':
                TenantProfile::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'status' => 'active',
                ]);
                break;
            case 'staff':
                StaffProfile::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'staff_type' => $request->staff_type,
                    'status' => 'active',
                ]);
                break;
            case 'super_admin':
                SuperAdminProfile::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'status' => 'active',
                ]);
                break;
        }

        return redirect()->route('super-admin.users')->with('success', 'User created successfully.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('super-admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:super_admin,landlord,tenant',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'business_info' => 'nullable|string|max:1000',
        ]);

        $user->update($request->only([
            'name', 'email', 'role', 'phone', 'address', 'business_info',
        ]));

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('super-admin.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function apartments()
    {
        $query = Property::with('landlord', 'units');

        // Search by property name or address
        if (request('search')) {
            $search = request('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        // Filter by status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Filter by landlord
        if (request('landlord')) {
            $query->where('landlord_id', request('landlord'));
        }

        $properties = $query->latest()->paginate(15);

        // Backward compatibility
        $apartments = $properties;

        return view('super-admin.apartments', compact('apartments', 'properties'));
    }

    public function properties()
    {
        return $this->apartments();
    }

    public function settings()
    {
        $settings = Setting::getGrouped();
        $groups = ['general', 'email', 'security', 'features', 'notifications', 'system'];

        // Apply dark mode to layout if enabled
        $darkMode = Setting::get('dark_mode', false);

        return view('super-admin.settings', compact('settings', 'groups', 'darkMode'));
    }

    public function checkDarkMode()
    {
        $darkMode = Setting::get('dark_mode', false);

        return response()->json(['darkMode' => $darkMode]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Handle different types
                if ($setting->type === 'boolean') {
                    $value = isset($value) && $value !== '0' && $value !== 'false';
                } elseif ($setting->type === 'integer') {
                    $value = (int) $value;
                } elseif ($setting->type === 'json' && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            }
        }

        // Clear cache
        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully.');
    }

    public function updateSettingsGroup(Request $request, $group)
    {
        $validGroups = ['general', 'email', 'security', 'features', 'notifications', 'system'];

        if (! in_array($group, $validGroups)) {
            return back()->with('error', 'Invalid settings group.');
        }

        $settings = Setting::getByGroup($group);
        $rules = [];

        foreach ($settings as $setting) {
            $rules["settings.{$setting->key}"] = $this->getValidationRule($setting);
        }

        $request->validate($rules);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->where('group', $group)->first();

            if ($setting) {
                // Handle different types
                if ($setting->type === 'boolean') {
                    $value = isset($value) && $value !== '0' && $value !== 'false';
                } elseif ($setting->type === 'integer') {
                    $value = (int) $value;
                } elseif ($setting->type === 'json' && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            }
        }

        // Clear cache
        Setting::clearCache();

        return back()->with('success', ucfirst($group).' settings updated successfully.');
    }

    protected function getValidationRule($setting)
    {
        $rules = [];

        switch ($setting->type) {
            case 'integer':
                $rules[] = 'nullable|integer';
                break;
            case 'boolean':
                $rules[] = 'nullable|boolean';
                break;
            case 'email':
                $rules[] = 'nullable|email';
                break;
            case 'url':
                $rules[] = 'nullable|url';
                break;
            default:
                $rules[] = 'nullable|string|max:1000';
        }

        return implode('|', $rules);
    }
}
