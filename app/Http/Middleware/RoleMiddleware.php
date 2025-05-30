<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have the required role to access this resource.'
                ], 403);
            }
            
            // Fallback for non-API requests
            return redirect('/')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
} 