<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de restriction d'accès aux routes médecin.
 *
 * Contrôle :
 * - Si l'utilisateur est authentifié → redirection vers /login sinon
 * - Si l'utilisateur a le rôle 'doctor' → HTTP 403 sinon
 *
 * Comportement en cas d'échec :
 * - Non authentifié : redirige vers la page de connexion
 * - Authentifié mais non médecin : retourne une erreur 403
 */
class DoctorMiddleware
{
    /**
     * Traite la requête entrante.
     *
     * @param  Request                    $request
     * @param  Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isDoctor()) {
            abort(403, 'Accès refusé. Vous devez être médecin.');
        }

        return $next($request);
    }
}
