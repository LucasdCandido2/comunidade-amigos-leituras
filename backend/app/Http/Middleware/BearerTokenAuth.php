<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class BearerTokenAuth
{
    /**
     * Handle an incoming request.
     * Autentica via Bearer token sem usar o Sanctum Guard (evita SIGSEGV no PHP-FPM).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token não fornecido'], 401);
        }

        // Busca o token no banco de dados
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Token inválido ou expirado'], 401);
        }

        $user = $accessToken->tokenable;

        // Atualizar last_used_at do token
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Também definir via Auth::setUser (sem session) para compatibilidade
        Auth::setUser($user);

        $response = $next($request);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Request completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'user_id' => $user->id,
            'duration_ms' => $duration,
            'status_code' => $response->getStatusCode(),
        ]);
        
        return $response;
    }
}

