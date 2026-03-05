<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('RoleMiddleware: User not authenticated', [
                'url' => $request->url(),
                'required_roles' => $roles
            ]);
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Add debugging for super admin access attempts
        if (in_array('super_admin', $roles)) {
            Log::info('RoleMiddleware: Super admin access attempt', [
                'url' => $request->url(),
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_status' => $user->status,
                'required_roles' => $roles,
                'role_check_passed' => in_array($user->role, $roles)
            ]);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            Log::warning('RoleMiddleware: Access denied', [
                'url' => $request->url(),
                'user_role' => $user->role,
                'required_roles' => $roles
            ]);
            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        // For landlords, check if they are approved
        if ($user->role === 'landlord' && $user->status !== 'approved') {
            if ($user->status === 'pending') {
                return redirect()->route('landlord.pending')->with('message', 'Your account is pending approval.');
            } elseif ($user->status === 'rejected') {
                return redirect()->route('landlord.rejected')->with('message', 'Your account has been rejected.');
            }
        }

        return $next($request);
    }
}
