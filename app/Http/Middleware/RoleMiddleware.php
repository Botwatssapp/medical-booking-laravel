<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware générique de contrôle de rôle.
 *
 * Usage dans les routes : `->middleware('role:admin,doctor')`
 *
 * Contrôle :
 * - Si l'utilisateur n'est pas connecté → redirection vers /login
 * - Si le rôle de l'utilisateur ne correspond pas aux rôles autorisés → HTTP 403
 *
 * Rôles disponibles : patient, doctor, admin
 * Plusieurs rôles peuvent être passés en paramètre séparés par une virgule.
 */
class RoleMiddleware
{
    /**
     * Traite la requête entrante.
     *
     * @param  Request                    $request  La requête HTTP entrante
     * @param  Closure(Request): Response $next     Le prochain middleware
     * @param  string                    ...$roles  Les rôles autorisés
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, $roles, strict: true)) {
            abort(403, 'Accès refusé. Vous n\'avez pas les permissions requises.');
        }

        return $next($request);
    }
}
