<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || !$request->user()->roles()->where('name', $role)->exists()) {
            return redirect()->route('dashboard')->with('error', 'عذراً، ليس لديك صلاحية الوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
