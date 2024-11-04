<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBearerToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->headers->has('Authorization')) {
            
            if ($request->has('token')) {
                $token = $request->query('token');
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
            else if ($request->hasCookie('auth_token')) {
                $token = $request->cookie('auth_token');
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        return $next($request);
    }
}
