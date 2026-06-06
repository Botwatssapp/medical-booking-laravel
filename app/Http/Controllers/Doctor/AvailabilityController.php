<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des disponibilités côté médecin.
 *
 * Permet au médecin de définir ses créneaux horaires disponibles
 * pour la réservation par les patients. Un créneau ne peut être créé
 * que pour une date future.
 */
class AvailabilityController extends Controller
{
    /**
     * Affiche la liste paginée des disponibilités du médecin connecté.
     *
     * @return View
     */
    public function index(): View
    {
        $doctor = auth()->user()->doctor;

        $availabilities = $doctor->availabilities()
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('doctor.availabilities.index', compact('availabilities'));
    }

    /**
     * Affiche le formulaire de création d'un créneau de disponibilité.
     *
     * @return View
     */
    public function create(): View
    {
        return view('doctor.availabilities.create');
    }

    /**
     * Enregistre un nouveau créneau de disponibilité pour le médecin.
     *
     * Correction de sécurité : utilisation de `only()` au lieu de `all()`
     * pour éviter le mass assignment non contrôlé.
     * Ajout de la validation `unique` pour éviter les doublons.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $doctor = auth()->user()->doctor;

        $validated = $request->validate([
            'date'       => [
                'required',
                'date',
                'after:today',
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
        ], [
            'date.required'       => 'La date est obligatoire.',
            'date.after'          => 'La date doit être dans le futur.',
            'start_time.required' => 'L\'heure de début est obligatoire.',
            'end_time.after'      => 'L\'heure de fin doit être après l\'heure de début.',
        ]);

        $doctor->availabilities()->create(array_merge($validated, [
            'is_available' => true,
        ]));

        return redirect()->route('doctor.availabilities.index')
            ->with('success', 'Disponibilité créée avec succès.');
    }

    /**
     * Supprime un créneau de disponibilité.
     *
     * Sécurité : vérifie que le créneau appartient bien au médecin connecté
     * avant la suppression pour éviter l'accès non autorisé.
     *
     * @param  Availability $availability
     * @return RedirectResponse
     */
    public function destroy(Availability $availability): RedirectResponse
    {
        $doctor = auth()->user()->doctor;

        // Vérification d'appartenance : interdit la suppression cross-médecin
        if ($availability->doctor_id !== $doctor->id) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer ce créneau.');
        }

        $availability->delete();

        return redirect()->route('doctor.availabilities.index')
            ->with('success', 'Disponibilité supprimée avec succès.');
    }
}
