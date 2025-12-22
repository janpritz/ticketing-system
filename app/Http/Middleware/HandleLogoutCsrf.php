<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class HandleLogoutCsrf
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF validation for logout if session is invalid
        if ($request->isMethod('post') && $request->routeIs('logout')) {
            // Check if session exists and has a valid CSRF token
            $sessionToken = $request->session()->token();
            $requestToken = $request->input('_token');
            
            if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) {
                // If CSRF validation fails, try to regenerate session and token
                $request->session()->regenerateToken();
                
                // Log the CSRF issue for debugging
                Log::warning('CSRF token mismatch on logout, regenerated token', [
                    'session_id' => $request->session()->getId(),
                    'has_session_token' => !empty($sessionToken),
                    'has_request_token' => !empty($requestToken),
                ]);
            }
        }
        
        return $next($request);
    }
}