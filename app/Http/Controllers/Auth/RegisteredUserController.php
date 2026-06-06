<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Gère l'inscription de nouveaux utilisateurs.
 *
 * Après inscription, l'utilisateur est redirigé vers le tableau de bord
 * correspondant à son rôle (patient ou médecin).
 * Les admins ne peuvent pas s'inscrire eux-mêmes ; leur compte est créé
 * directement par un administrateur existant.
 */
class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traite la requête d'inscription.
     *
     * Valide le rôle (patient ou médecin uniquement), crée l'utilisateur,
     * déclenche l'événement Registered, connecte l'utilisateur,
     * puis redirige vers le tableau de bord approprié.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'string', 'in:patient,doctor'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect($this->dashboardRouteFor($user));
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
