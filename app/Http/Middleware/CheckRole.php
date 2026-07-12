<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Barra a requisição se o usuário autenticado não possuir nenhuma das roles exigidas.
     * As roles vêm como parâmetros do middleware na rota (ex: 'role:admin,recruiter').
     * Deve ser usado sempre após 'auth:sanctum' — depende do usuário já autenticado.
     *
     * @param  Request $request
     * @param  Closure(Request): (Response) $next
     * @param  string ...$roles Slugs de role aceitas para a rota
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 se o usuário não tiver a role
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole($roles))
            abort(403, 'Usuário não tem a função necessária para acessar esse recurso.');

        return $next($request);
    }
}
