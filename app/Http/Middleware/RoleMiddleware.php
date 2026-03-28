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
        if (! Auth::check()) {
            Log::warning('RoleMiddleware: User not authenticated', [
                'url' => $request->url(),
                'required_roles' => $roles,
            ]);

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Guard against corrupted or unrecognized role values before any access decision.
        // Known roles are the four that the application understands. Any other value in
        // the database column (null, typo, leftover migration artifact) is a data-integrity
        // problem that should never silently pass an authorization check.
        $knownRoles = ['tenant', 'landlord', 'staff', 'super_admin'];
        if (! in_array($user->role, $knownRoles, strict: true)) {
            Log::error('RoleMiddleware: User carries unrecognized role value', [
                'user_id'        => $user->id,
                'role'           => $user->role,
                'url'            => $request->url(),
                'required_roles' => $roles,
            ]);
            abort(403, 'Access denied: unrecognized role.');
        }

        // Add debugging for super admin access attempts
        if (in_array('super_admin', $roles)) {
            Log::info('RoleMiddleware: Super admin access attempt', [
                'url'              => $request->url(),
                'user_id'          => $user->id,
                'user_role'        => $user->role,
                'user_status'      => $user->status,
                'required_roles'   => $roles,
                'role_check_passed' => in_array($user->role, $roles),
            ]);
        }

        // Check if user has any of the required roles
        if (! in_array($user->role, $roles)) {
            Log::warning('RoleMiddleware: Access denied', [
                'url'            => $request->url(),
                'user_role'      => $user->role,
                'required_roles' => $roles,
            ]);
            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        // For landlords, check if they are approved.
        // Known statuses: approved (pass), pending, rejected (redirect).
        // Any other value is treated as denied — the else branch prevents an
        // unrecognized status from silently falling through to $next($request).
        if ($user->role === 'landlord' && $user->status !== 'approved') {
            if ($user->status === 'pending') {
                return redirect()->route('landlord.pending')->with('message', 'Your account is pending approval.');
            } elseif ($user->status === 'rejected') {
                return redirect()->route('landlord.rejected')->with('message', 'Your account has been rejected.');
            } else {
                Log::error('RoleMiddleware: Landlord has unrecognized status value', [
                    'user_id' => $user->id,
                    'status'  => $user->status,
                    'url'     => $request->url(),
                ]);
                abort(403, 'Access denied: unrecognized account status.');
            }
        }

        return $next($request);
    }
}
