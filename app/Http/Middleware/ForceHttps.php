<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTPS for all requests when not in local development and not localhost
        if (!$request->secure() && !app()->environment('local') && $request->getHost() !== 'localhost') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}