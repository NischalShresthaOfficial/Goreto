<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow access to login page
        if ($request->is('admin/login')) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            abort(403, 'Unauthorized access - Please login first');
        }

        $user = Auth::user();

        // Check if user has required roles
        if ($user->hasRole(['super_admin', 'admin', 'panel_user'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized access - Insufficient permissions');
    }
}
