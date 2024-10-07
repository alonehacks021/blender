<?php

namespace Nhd\Foundation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nhd\Foundation\Services\AuthService;

class CentralAuth
{
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->check()) {
            return $this->login($request, $next);
        }

        return $next($request);
    }

    private function login($request, $next) {
        $authorized = $request->get('code');
        $csrfCode = $request->get('state');

        if((!$authorized) || (!$csrfCode)) {
            return AuthService::forward(); 
        }

        return $next($request);
    }
}
