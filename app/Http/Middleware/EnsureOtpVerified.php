<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // In your EnsureOtpVerified Middleware
    public function handle(Request $request, Closure $next)
    {
        // 1. Get verified email from session or cookie
        $verifiedEmail = session('verified_email') ?? $request->cookie('verified_email');

        // 2. IDENTIFIER RESOLUTION
        // Check route param first (recepient_id), then query param (email)
        $identifier = $request->route('recepient_id') ?? $request->query('email');

        // 3. EXISTENCE CHECK
        if (!$verifiedEmail || $verifiedEmail === 'deleted') {
            return redirect()->route('tickets.verify', ['email' => $identifier])
                ->with('error', 'Your session has expired.');
        }

        // 4. SECURITY & SYNC CHECK
        // If an identifier exists in the URL, it MUST match the verified email
        if ($identifier && $identifier !== $verifiedEmail) {
            return redirect()->route('tickets.verify', ['email' => $identifier])
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
