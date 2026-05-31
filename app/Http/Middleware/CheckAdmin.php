<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->tipo_perfil !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.',
            ], 403);
        }

        return $next($request);
    }
}
