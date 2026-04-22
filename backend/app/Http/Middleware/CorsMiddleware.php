<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        $allowedOrigins = array_filter(
            array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))
        );

        $allowOrigin = '*';
        $allowCredentials = false;

        if ($allowedOrigins !== []) {
            if ($origin && in_array($origin, $allowedOrigins, true)) {
                $allowOrigin = $origin;
                $allowCredentials = true;
            }
        } elseif ($origin) {
            // Dev-friendly default when no allowlist is configured.
            $allowOrigin = $origin;
            $allowCredentials = true;
        }

        // Preflight
        if ($request->isMethod('OPTIONS')) {
            $response = response()->noContent(204);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Vary', 'Origin');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization,Content-Type,X-Requested-With,X-CSRF-Token,X-User-Id');
        $response->headers->set('Access-Control-Allow-Credentials', $allowCredentials ? 'true' : 'false');

        return $response;
    }
}

