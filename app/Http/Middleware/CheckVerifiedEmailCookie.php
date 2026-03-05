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
        $verifiedEmail = $request->cookie('verified_email');

        // 1. If they enter the URL exactly as /tickets/verify-otp, 
        // redirect them to include a random 6-digit identifier.
        if ($request->is('tickets/verify-otp') && !$request->route('identifier')) {
            $randomId = rand(100000, 999999);

            // Build the new URL with the random ID and keep the email cookie if it exists
            $query = $verifiedEmail ? ['email' => $verifiedEmail] : [];

            return redirect()->to(url("tickets/verify-otp/{$randomId}", $query));
        }

        // 2. Safe Zone: If it ALREADY has an identifier, let it pass through.
        if ($request->is('tickets/verify-otp/*')) {
            return $next($request);
        }

        // 3. Standard logic for other /tickets routes
        if ($verifiedEmail && !$request->query('email')) {
            return redirect()->to($request->fullUrlWithQuery(['email' => $verifiedEmail]));
        }

        return $next($request);
    }
}
