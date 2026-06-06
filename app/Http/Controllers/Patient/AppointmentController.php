<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des rendez-vous côté patient.
 *
 * Permet au patient de consulter, créer, modifier et annuler ses rendez-vous.
 * La règle métier principale : un patient ne peut annuler qu'un rendez-vous futur.
 * Lors de l'annulation, le créneau de disponibilité est libéré.
 */
class AppointmentController extends Controller
{
    /**
     * Affiche la liste paginée des rendez-vous du patient connecté.
     *
     * Chargement eager des relations doctor, user et speciality
     * pour éviter les requêtes N+1.
     *
     * @return View
     */
    public function index(): View
    {
        $appointments = auth()->user()->appointments()
            ->with(['doctor.user', 'doctor.speciality'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);

        return view('patient.appointments.index', compact('appointments'));
    }

    /**
     * Affiche le formulaire de création d'un nouveau rendez-vous.
     *
     * @return View
     */
    public function create(): View
    {
        return view('patient.appointments.create');
    }

    /**
     * Enregistre un nouveau rendez-vous pour le patient.
     *
     * Marque la disponibilité comme non disponible dans une transaction
     * pour éviter les doubles réservations.
     *
     * @param  StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            auth()->user()->appointments()->create($data);

            // Marquer le créneau comme réservé (non disponible)
            if (!empty($data['availability_id'])) {
                \App\Models\Availability::where('id', $data['availability_id'])
                    ->update(['is_available' => false]);
            }
        });

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Rendez-vous créé avec succès.');
    }

    /**
     * Affiche le détail d'un rendez-vous du patient.
     *
     * Contrôle d'autorisation via la Policy AppointmentPolicy.
     *
     * @param  Appointment $appointment
     * @return View
     */
    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['doctor.user', 'doctor.speciality', 'availability']);

        return view('patient.appointments.show', compact('appointment'));
    }

    /**
     * Affiche le formulaire de modification d'un rendez-vous.
     *
     * Seuls les rendez-vous futurs peuvent être modifiés.
     *
     * @param  Appointment $appointment
     * @return View
     */
    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        return view('patient.appointments.edit', compact('appointment'));
    }

    /**
     * Met à jour un rendez-vous existant.
     *
     * Un patient ne peut modifier que ses rendez-vous futurs.
     *
     * @param  UpdateAppointmentRequest $request
     * @param  Appointment              $appointment
     * @return RedirectResponse
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        if ($appointment->appointment_date->isPast()) {
            return back()->with('error', 'Impossible de modifier un rendez-vous passé.');
        }

        $appointment->update($request->validated());

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Rendez-vous modifié avec succès.');
    }

    /**
     * Annule un rendez-vous du patient.
     *
     * Règle métier : annulation uniquement avant la date du rendez-vous.
     * Le créneau de disponibilité est libéré dans une transaction atomique.
     *
     * @param  Appointment $appointment
     * @return RedirectResponse
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        if ($appointment->appointment_date->isPast()) {
            return back()->with('error', 'Impossible d\'annuler un rendez-vous passé.');
        }

        DB::transaction(function () use ($appointment) {
            // Libérer le créneau de disponibilité associé
            if ($appointment->availability_id) {
                \App\Models\Availability::where('id', $appointment->availability_id)
                    ->update(['is_available' => true]);
            }

            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        });

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }
}
