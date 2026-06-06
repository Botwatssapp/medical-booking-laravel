<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Accès refusé');
        }

        return $next($request);
    }
}
