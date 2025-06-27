<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$request->user()->can($permission)) {
            // Check if this is an API or AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Access denied. Required permission: ' . $permission,
                    'required_permission' => $permission,
                    'user_permissions' => $request->user()->getAllPermissions()->pluck('name')
                ], 403);
            }
            
            // For Inertia requests (check multiple headers and conditions)
            if ($request->header('X-Inertia') || 
                $request->header('X-Inertia-Version') || 
                $request->wantsJson() ||
                str_contains($request->header('Accept', ''), 'text/html')) {
                    
                return Inertia::render('Errors/403', [
                    'message' => 'Access denied. You need "' . $permission . '" permission to access this page.',
                    'required_permission' => $permission,
                    'user_permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    'status' => 403
                ])->toResponse($request)->setStatusCode(403);
            }
            
            // Fallback for any other requests - also use Inertia page
            return Inertia::render('Errors/403', [
                'message' => 'Access denied. You need "' . $permission . '" permission to access this page.',
                'required_permission' => $permission,
                'user_permissions' => $request->user()->getAllPermissions()->pluck('name'),
                'status' => 403
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
