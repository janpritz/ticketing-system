<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerifiedEmailCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the verified_email cookie
        $verifiedEmail = $request->cookie('verified_email');
        
        // If verified_email cookie exists and no email parameter in URL, redirect with email parameter
        if ($verifiedEmail && !$request->has('email')) {
            // For /tickets/verify-otp, redirect to /tickets with email parameter
            if ($request->path() === 'tickets/verify-otp' || strpos($request->path(), 'tickets/verify-otp/') === 0) {
                return redirect()->to('/tickets?email=' . urlencode($verifiedEmail));
            }
            
            // For other /tickets routes, append email parameter
            return redirect()->to($request->path() . '?email=' . urlencode($verifiedEmail));
        }
        
        return $next($request);
    }
}
