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
        $cookieEmail = $request->cookie('verified_email');

        // 1. EXISTENCE CHECK
        if (!$cookieEmail || $cookieEmail === 'deleted') {
            return redirect()->route('tickets.verify-otp')
                ->with('error', 'Your session has expired.');
                // ->withoutCookie('verified_email');
        }

        // 2. IDENTIFIER RESOLUTION
        // Check route param first (recepient_id), then query param (email)
        $identifier = $request->route('recepient_id') ?? $request->query('email');

        // 3. SECURITY & SYNC CHECK
        // If an identifier exists in the URL, it MUST match the cookie email
        // (Note: This assumes recepient_id is the email. If it's a numeric ID, you'd need your service here)
        if ($identifier && $identifier !== $cookieEmail) {
            return redirect()->route('tickets.verify-otp')
                ->with('error', 'Unauthorized access.');
                // ->withoutCookie('verified_email');
        }

        return $next($request);
    }
}
