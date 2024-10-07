<?php

namespace Nhd\Foundation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmergencyLoginHandle
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
        if(config('auth.emergency_login') != 'YES') {
            return abort(404);
        }

        return $next($request);
    }
}
