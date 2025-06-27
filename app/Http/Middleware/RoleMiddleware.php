<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$request->user()->hasRole($role)) {
            // Check if this is an API or AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Access denied. Required role: ' . $role,
                    'required_role' => $role,
                    'user_roles' => $request->user()->getRoleNames()
                ], 403);
            }
            
            // For Inertia requests (check multiple headers and conditions)
            if ($request->header('X-Inertia') || 
                $request->header('X-Inertia-Version') || 
                $request->wantsJson() ||
                str_contains($request->header('Accept', ''), 'text/html')) {
                    
                return Inertia::render('Errors/403', [
                    'message' => 'Access denied. You need "' . $role . '" role to access this page.',
                    'required_role' => $role,
                    'user_roles' => $request->user()->getRoleNames(),
                    'status' => 403
                ])->toResponse($request)->setStatusCode(403);
            }
            
            // Fallback for any other requests - also use Inertia page
            return Inertia::render('Errors/403', [
                'message' => 'Access denied. You need "' . $role . '" role to access this page.',
                'required_role' => $role,
                'user_roles' => $request->user()->getRoleNames(),
                'status' => 403
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
