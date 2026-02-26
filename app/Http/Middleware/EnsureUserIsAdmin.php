<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRole;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user has the Primary Administrator role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login to access this page.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Check if user has role_id = 1 (Primary Administrator) in user_roles table
        $isAdmin = $user && UserRole::where('user_id', $user->id)
            ->where('role_id', 1)
            ->exists();

        if (!$isAdmin) {
            // User is not an admin - redirect to staff dashboard with error
            return redirect()->route('staff.dashboard')
                ->with('error', 'You do not have permission to access admin pages.');
        }

        return $next($request);
    }
}
