<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificaAssinatura
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin nao precisa de assinatura
        if ($user->papel === 'admin') {
            return $next($request);
        }

        $locador = $user->locador();

        // Verifica se o locador tem acesso ativo
        if (! $locador || ! $locador->hasAcessoAtivo()) {
            return response()->json([
                'message' => 'Assinatura expirada. Renove para continuar acessando.',
                'code' => 'subscription_expired',
            ], 403);
        }

        return $next($request);
    }
}
