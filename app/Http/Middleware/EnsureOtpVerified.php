<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if session has OTP verification
        if (!session('otp_verified')) {
            return redirect()->route('tickets.verify-otp');
        }

        // Check if OTP verification has expired (30 minutes)
        $verifiedAt = session('otp_verified_at');
        if ($verifiedAt && now()->diffInMinutes($verifiedAt) > 30) {
            // Session expired, forget all OTP-related session values
            session()->forget(['otp_verified', 'verified_email', 'otp_verified_at']);
            return redirect()->route('tickets.verify-otp')->with('error', 'Session expired. Please verify again.');
        }

        return $next($request);
    }
}
