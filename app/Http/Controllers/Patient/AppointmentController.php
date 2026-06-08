<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): View
    {
        $query = auth()->user()->appointments()
            ->with(['doctor.user', 'doctor.speciality', 'availability']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $direction    = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $appointments = $query->orderBy('appointment_date', $direction)->paginate(10)->withQueryString();

        return view('patient.appointments.index', compact('appointments'));
    }

    /**
     * Affiche le formulaire de confirmation de rendez-vous.
     *
     * Charge la disponibilité sélectionnée depuis le profil du médecin.
     * Si le créneau n'est plus disponible ou n'existe pas, renvoie null
     * et la vue affiche un message d'erreur.
     *
     * @param  Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $availability = null;

        if ($request->filled('availability_id')) {
            $availability = Availability::with(['doctor.user', 'doctor.speciality'])
                ->available()
                ->find($request->integer('availability_id'));
        }

        return view('patient.appointments.create', compact('availability'));
    }

    /**
     * Enregistre un nouveau rendez-vous pour le patient.
     *
     * `appointment_date` est dérivé de la disponibilité (date + start_time)
     * et n'est donc pas soumis via le formulaire — évite tout décalage.
     *
     * `lockForUpdate()` pose un verrou exclusif sur la ligne de disponibilité
     * dans la transaction pour prévenir les doubles réservations simultanées.
     *
     * @param  StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Verrou exclusif : empêche la double réservation concurrente
            $availability = Availability::lockForUpdate()
                ->where('id', $data['availability_id'])
                ->where('is_available', true)
                ->firstOrFail();

            // Dériver appointment_date depuis la disponibilité (date + heure de début)
            $data['appointment_date'] = $availability->date->format('Y-m-d')
                . ' ' . $availability->start_time;

            /** @var \App\Models\User $patient */
            $patient = auth()->user();
            $patient->appointments()->create($data);

            $availability->update(['is_available' => false]);
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
