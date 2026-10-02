<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\AppointmentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Availability;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des rendez-vous côté patient.
 *
 * Orchestration uniquement : autorisation, validation, appel du service.
 */
class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    /**
     * Affiche la liste paginée des rendez-vous du patient connecté.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()->appointments()
            ->with(['doctor.user', 'doctor.speciality', 'availability']);

        $allowedStatuses = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_ACCEPTED,
            Appointment::STATUS_REJECTED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_MISSED,
        ];

        if ($request->filled('status') && in_array($request->status, $allowedStatuses, true)) {
            $query->where('status', $request->status);
        }

        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $appointments = $query->orderBy('appointment_date', $direction)->paginate(10)->withQueryString();

        return view('patient.appointments.index', compact('appointments'));
    }

    /**
     * Affiche le formulaire de confirmation de rendez-vous.
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
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->appointments->create(
                $request->user(),
                (int) $data['doctor_id'],
                (int) $data['availability_id'],
                $data['notes'] ?? null,
            );
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Demande de rendez-vous envoyée. Elle est en attente de confirmation.');
    }

    /**
     * Affiche le détail d'un rendez-vous du patient connecté.
     */
    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['doctor.user', 'doctor.speciality', 'availability']);

        return view('patient.appointments.show', compact('appointment'));
    }

    /**
     * Formulaire de notes uniquement (pas de report de date).
     */
    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        $appointment->load(['doctor.user', 'doctor.speciality', 'availability']);

        return view('patient.appointments.edit', compact('appointment'));
    }

    /**
     * Met à jour un rendez-vous existant (notes, ou annulation).
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validated();

        if (($data['status'] ?? null) === Appointment::STATUS_CANCELLED) {
            $this->authorize('cancel', $appointment);

            try {
                $this->appointments->cancel($appointment, requireFuture: true);
            } catch (AppointmentException $e) {
                return back()->with('error', $e->getMessage());
            }

            return redirect()->route('patient.appointments.show', $appointment)
                ->with('success', 'Rendez-vous annulé avec succès.');
        }

        if ($appointment->appointment_date->isPast()) {
            return back()->with('error', 'Impossible de modifier un rendez-vous passé.');
        }

        $appointment->update(array_intersect_key($data, array_flip(['notes'])));

        return redirect()->route('patient.appointments.show', $appointment)
            ->with('success', 'Notes mises à jour.');
    }

    /**
     * Annule un rendez-vous du patient (futurs uniquement).
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('cancel', $appointment);

        try {
            $this->appointments->cancel($appointment, requireFuture: true);
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }
}
