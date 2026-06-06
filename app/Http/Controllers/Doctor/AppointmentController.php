<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des rendez-vous côté médecin.
 *
 * Permet au médecin de consulter ses rendez-vous, de les accepter,
 * les rejeter ou les annuler. Lors de l'annulation, le créneau
 * de disponibilité est libéré.
 */
class AppointmentController extends Controller
{
    /**
     * Affiche la liste paginée des rendez-vous du médecin connecté.
     *
     * Chargement eager de la relation patient pour éviter les requêtes N+1.
     *
     * @return View
     */
    public function index(): View
    {
        $doctor = auth()->user()->doctor;

        $appointments = $doctor->appointments()
            ->with(['patient', 'availability'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(15);

        return view('doctor.appointments.index', compact('appointments'));
    }

    /**
     * Affiche le détail d'un rendez-vous.
     *
     * Contrôle d'autorisation via la Policy AppointmentPolicy.
     *
     * @param  Appointment $appointment
     * @return View
     */
    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'availability', 'doctor.speciality']);

        return view('doctor.appointments.show', compact('appointment'));
    }

    /**
     * Met à jour le statut ou les notes d'un rendez-vous.
     *
     * Utilise `validated()` pour garantir que seuls les champs validés
     * sont persistés (pas de mass assignment ouvert).
     *
     * @param  UpdateAppointmentRequest $request
     * @param  Appointment              $appointment
     * @return RedirectResponse
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    /**
     * Annule un rendez-vous du médecin.
     *
     * Libère le créneau de disponibilité dans une transaction atomique.
     *
     * @param  Appointment $appointment
     * @return RedirectResponse
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        DB::transaction(function () use ($appointment) {
            // Libérer le créneau de disponibilité associé
            if ($appointment->availability_id) {
                \App\Models\Availability::where('id', $appointment->availability_id)
                    ->update(['is_available' => true]);
            }

            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        });

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }
}
