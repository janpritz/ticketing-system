<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\{UserRole};

class EnsureIsStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 1. Check if user is logged in
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // 2. Check if user has the Admin role (ID = 1)
        $isAdmin = UserRole::where('user_id', $user->id)
            ->where('role_id', 1)
            ->exists();

        // 3. If they ARE an admin, they shouldn't be in the staff area
        if ($isAdmin) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Administrators are redirected to the Admin panel.');
        }

        return $next($request);
    }
}