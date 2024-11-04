<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAuthenticate
{
    public function handle(Request $request, Closure $next, $user_level = null)
    {
        //if (!$this->isValidProfile($profile))
            //throw new InvalidProfileException();

        if (!Auth::check())
            return response()->json(['message' => 'Unauthorized'], 401);

        $user = Auth::user();
        if ($user_level && $user->user_level < (int)$user_level)
            return response()->json(['message' => 'Forbidden'], 403);

        return $next($request);
    }

    protected function isValidProfile($user_level)
    {
        // Permite que o perfil seja vazio ou nulo
        if (is_null($user_level) || $user_level === '') {
            return true;
        }

        // Verifica se é um número inteiro entre 1 e 9
        return is_numeric($user_level) && (int) $user_level >= 1 && (int) $user_level <= 9 && (int) $user_level == $user_level;
    }
}
