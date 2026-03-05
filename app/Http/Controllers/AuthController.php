<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Property;
use App\Models\TenantProfile;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        // Show a landing panel with a few latest properties and available units
        $properties = Property::with(['units' => function ($q) {
            $q->where('status', 'available');
        }])->where('is_active', true)->latest()->take(8)->get();

        return view('login', compact('properties'));
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Redirect based on user role
            $user = Auth::user();
            
            // Add debugging for super admin case
            if ($user->role === 'super_admin') {
                // Log the super admin login attempt
                Log::info('Super admin login attempt', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_role' => $user->role,
                    'user_status' => $user->status,
                    'redirect_route' => route('super-admin.dashboard')
                ]);
            }
            
            switch ($user->role) {
                case 'super_admin':
                    return redirect()->route('super-admin.dashboard');
                case 'landlord':
                    if ($user->status === 'approved') {
                        return redirect()->route('landlord.dashboard');
                    } elseif ($user->status === 'pending') {
                        return redirect()->route('landlord.pending');
                    } else {
                        return redirect()->route('landlord.rejected');
                    }
                case 'tenant':
                    return redirect()->route('tenant.dashboard');
                case 'staff':
                    return redirect()->route('staff.dashboard');
                default:
                    return redirect()->route('dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle tenant registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create user without name (name is stored in profile)
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'tenant',
        ]);

        // Create tenant profile with the actual name
        TenantProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->first_name . ' ' . $request->last_name,
                'status' => 'active',
                'phone' => null,
                'address' => null,
            ]
        );

        Auth::login($user);

        return redirect()->route('tenant.dashboard');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        // Clear any authentication data
        Auth::logout();
        
        // Clear session data
        Session::flush();
        
        // Clear any stored sidebar state
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        }
        
        // Redirect to login page
        return redirect()->route('login')->with('message', 'You have been logged out successfully.');
    }
} 