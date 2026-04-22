<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  string  ...$roles Allowed roles (e.g. STUDENT,ADMIN)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userRole = strtoupper((string) $request->user()->role);
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Be tolerant to "STUDENT,ADMIN" being passed as a single parameter.
            foreach (explode(',', (string) $role) as $single) {
                $single = strtoupper(trim($single));
                if ($single !== '') {
                    $allowedRoles[] = $single;
                }
            }
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}

