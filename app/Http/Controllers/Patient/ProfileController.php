<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur du profil patient.
 *
 * Permet au patient de consulter et modifier ses informations personnelles.
 * Seuls les champs présents dans la table users sont mis à jour ici.
 */
class ProfileController extends Controller
{
    /**
     * Affiche le formulaire de modification du profil patient.
     *
     * @return View
     */
    public function edit(): View
    {
        $user = auth()->user();

        return view('patient.profile.edit', compact('user'));
    }

    /**
     * Met à jour les informations du profil du patient connecté.
     *
     * Correction : suppression de `phone` et `address` qui n'existent pas
     * dans la table `users` (ils appartiennent à la table `doctors`).
     * L'unicité de l'email ignore l'utilisateur courant.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('patient.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }
}
