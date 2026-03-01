<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableDataCaching
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only disable caching for HTML responses and API/data responses
        // Allow caching for assets (CSS, JS, images, etc.)
        $contentType = $response->headers->get('Content-Type', '');
        
        if (str_contains($contentType, 'text/html') || 
            str_contains($contentType, 'application/json') ||
            str_contains($contentType, 'text/plain') ||
            $request->is('api/*') ||
            $request->is('admin*') ||
            $request->is('staff/*')) {
            
            // Disable caching for HTML and data responses
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}