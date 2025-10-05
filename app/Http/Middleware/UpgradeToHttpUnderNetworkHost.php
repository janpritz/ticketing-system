<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;

class UpgradeToHttpUnderNetworkHost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHttpHost();

        // If accessed through your local network IP (with or without port)
        if ($host === '192.168.254.191:8000' || $host === '192.168.254.191') {
            // Force Laravel to generate URLs using your LAN IP and HTTP scheme
            URL::forceRootUrl('http://192.168.254.191:8000');
            URL::forceScheme('http'); // ✅ Use HTTP, not HTTPS
        }

        return $next($request);
    }
}
