<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureStaffCanAccessTicket
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // Retrieve the 'ticket' model from the route parameter
        $ticket = $request->route('ticket');

        if (!$auth || !$ticket) {
            return $this->unauthorized($request);
        }

        // Logic: Must be assigned staff OR Primary Admin
        if ($ticket->staff_id !== $auth->id && !$auth->isPrimaryAdmin()) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    protected function unauthorized(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        abort(403);
    }
}
