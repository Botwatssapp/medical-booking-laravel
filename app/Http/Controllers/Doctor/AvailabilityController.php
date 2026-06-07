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
            ->paginate(20);

        $stats = [
            'total'     => $doctor->availabilities()->count(),
            'available' => $doctor->availabilities()->where('is_available', true)->count(),
            'booked'    => $doctor->availabilities()->where('is_available', false)->count(),
        ];

        return view('doctor.availabilities.index', compact('availabilities', 'stats'));
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

        // ── Mode génération automatique ───────────────────────────────────────
        if ($request->input('mode') === 'generate') {
            $validated = $request->validate([
                'date'             => ['required', 'date', 'after:today'],
                'period_start'     => ['required', 'date_format:H:i'],
                'period_end'       => ['required', 'date_format:H:i', 'after:period_start'],
                'duration_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
            ], [
                'date.after'           => 'La date doit être dans le futur.',
                'period_end.after'     => 'La fin de plage doit être après le début.',
                'duration_minutes.in'  => 'Durée invalide. Choisissez parmi 15, 20, 30, 45 ou 60 min.',
            ]);

            $duration = (int) $validated['duration_minutes'];
            [$startH, $startM] = array_map('intval', explode(':', $validated['period_start']));
            [$endH,   $endM]   = array_map('intval', explode(':', $validated['period_end']));
            $curMin = $startH * 60 + $startM;
            $endMin = $endH   * 60 + $endM;

            $created = 0;
            while ($curMin + $duration <= $endMin) {
                $slotStart = sprintf('%02d:%02d', intdiv($curMin, 60), $curMin % 60);
                $slotEnd   = sprintf('%02d:%02d', intdiv($curMin + $duration, 60), ($curMin + $duration) % 60);

                // Évite les doublons
                $exists = $doctor->availabilities()
                    ->where('date', $validated['date'])
                    ->where('start_time', $slotStart)
                    ->exists();

                if (!$exists) {
                    $doctor->availabilities()->create([
                        'date'         => $validated['date'],
                        'start_time'   => $slotStart,
                        'end_time'     => $slotEnd,
                        'is_available' => true,
                    ]);
                    $created++;
                }

                $curMin += $duration;
            }

            return redirect()->route('doctor.availabilities.index')
                ->with('success', "$created créneau(x) créé(s) avec succès.");
        }

        // ── Mode créneau unique ───────────────────────────────────────────────
        $validated = $request->validate([
            'date'       => ['required', 'date', 'after:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
        ], [
            'date.after'      => 'La date doit être dans le futur.',
            'end_time.after'  => 'L\'heure de fin doit être après l\'heure de début.',
        ]);

        // Doublon ?
        $exists = $doctor->availabilities()
            ->where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['start_time' => 'Un créneau existe déjà à cette heure pour cette date.'])
                ->withInput();
        }

        $doctor->availabilities()->create(array_merge($validated, ['is_available' => true]));

        return redirect()->route('doctor.availabilities.index')
            ->with('success', 'Créneau créé avec succès.');
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
