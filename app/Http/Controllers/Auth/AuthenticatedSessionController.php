<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gère l'authentification des utilisateurs (connexion / déconnexion).
 *
 * Après connexion, redirige vers le tableau de bord correspondant au rôle
 * de l'utilisateur (admin, médecin ou patient).
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Traite la requête d'authentification.
     *
     * Authentifie l'utilisateur, régénère la session pour prévenir
     * la fixation de session, puis redirige vers le tableau de bord
     * correspondant au rôle.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return redirect()->intended($this->dashboardRouteFor($user));
    }

    /**
     * Détruit la session authentifiée (déconnexion).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Retourne l'URL du tableau de bord selon le rôle de l'utilisateur.
     */
    private function dashboardRouteFor(User $user): string
    {
        return match ($user->role) {
            'admin'  => route('admin.dashboard',   absolute: false),
            'doctor' => route('doctor.dashboard',  absolute: false),
            default  => route('patient.dashboard', absolute: false),
        };
    }
}
