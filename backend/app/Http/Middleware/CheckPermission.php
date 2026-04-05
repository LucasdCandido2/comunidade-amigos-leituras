<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission($permission)) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        return $next($request);
    }
}