<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\LandlordProfile;
use App\Models\StaffProfile;
use App\Models\SuperAdminProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with('approvedBy');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($query) use ($search) {
                $query->where('email', 'like', '%'.$search.'%')
                    ->orWhereHas('landlordProfile', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('tenantProfile', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('staffProfile', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('superAdminProfile', fn ($q) => $q->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (request('role')) {
            $query->where('role', request('role'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $users = $query->latest()->paginate(15);

        return view('super-admin.users', compact('users'));
    }

    public function create()
    {
        return view('super-admin.create-user');
    }

    public function store(StoreUserRequest $request)
    {

        DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            switch ($request->role) {
                case 'landlord':
                    LandlordProfile::updateOrCreate(['user_id' => $user->id], [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'business_info' => $request->business_info,
                        'status' => 'pending',
                    ]);
                    if ($request->boolean('approve_immediately')) {
                        $user->approve(Auth::id());
                    }
                    break;
                case 'tenant':
                    TenantProfile::updateOrCreate(['user_id' => $user->id], [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'status' => 'active',
                    ]);
                    break;
                case 'staff':
                    StaffProfile::updateOrCreate(['user_id' => $user->id], [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'staff_type' => $request->staff_type,
                        'status' => 'active',
                    ]);
                    break;
                case 'super_admin':
                    SuperAdminProfile::updateOrCreate(['user_id' => $user->id], [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'status' => 'active',
                    ]);
                    break;
            }
        });

        return redirect()->route('super-admin.users')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('super-admin.edit-user', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->only(['email', 'role']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $profileData = $request->only(['name', 'phone', 'address']);

        if ($user->role === 'landlord' && $request->filled('business_info')) {
            $profileData['business_info'] = $request->business_info;
        }

        switch ($user->role) {
            case 'landlord':
                LandlordProfile::updateOrCreate(['user_id' => $user->id], $profileData);
                break;
            case 'tenant':
                TenantProfile::updateOrCreate(['user_id' => $user->id], $profileData);
                break;
            case 'staff':
                if ($request->filled('staff_type')) {
                    $profileData['staff_type'] = $request->staff_type;
                }
                StaffProfile::updateOrCreate(['user_id' => $user->id], $profileData);
                break;
            case 'super_admin':
                SuperAdminProfile::updateOrCreate(['user_id' => $user->id], $profileData);
                break;
        }

        return redirect()->route('super-admin.users')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
