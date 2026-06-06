<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Contrôleur du profil médecin.
 *
 * Permet au médecin de consulter et mettre à jour ses informations
 * professionnelles : téléphone, adresse, biographie et photo.
 */
class ProfileController extends Controller
{
    /**
     * Affiche le formulaire de modification du profil médecin.
     *
     * @return View
     */
    public function edit(): View
    {
        $doctor = auth()->user()->doctor;

        return view('doctor.profile.edit', compact('doctor'));
    }

    /**
     * Met à jour le profil du médecin connecté.
     *
     * Correction : utilisation de l'injection de dépendance `Request`
     * au lieu du helper global `request()`.
     * L'ancienne photo est supprimée du storage avant le remplacement.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $doctor = auth()->user()->doctor;

        $validated = $request->validate([
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio'     => ['nullable', 'string', 'max:1000'],
            'photo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            // Suppression de l'ancienne photo pour éviter les fichiers orphelins
            if ($doctor->photo) {
                Storage::disk('public')->delete($doctor->photo);
            }
            $validated['photo'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($validated);

        return redirect()->route('doctor.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }
}
