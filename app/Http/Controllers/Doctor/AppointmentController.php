<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\AppointmentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            abort(403, 'Votre profil médecin n\'est pas encore validé.');
        }

        $query = $doctor->appointments()->with(['patient', 'availability']);

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
        $appointments = $query->orderBy('appointment_date', $direction)->paginate(15)->withQueryString();

        return view('doctor.appointments.index', compact('appointments'));
    }

    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'availability', 'doctor.speciality']);

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validated();
        $status = $data['status'] ?? null;

        try {
            match ($status) {
                Appointment::STATUS_ACCEPTED => $this->applyDoctorStatus($appointment, 'accept', fn () => $this->appointments->accept($appointment)),
                Appointment::STATUS_REJECTED => $this->applyDoctorStatus($appointment, 'reject', fn () => $this->appointments->reject($appointment)),
                Appointment::STATUS_COMPLETED => $this->applyDoctorStatus($appointment, 'complete', fn () => $this->appointments->complete($appointment)),
                Appointment::STATUS_MISSED => $this->applyDoctorStatus($appointment, 'markMissed', fn () => $this->appointments->markMissed($appointment)),
                Appointment::STATUS_CANCELLED => $this->applyDoctorStatus($appointment, 'cancel', fn () => $this->appointments->cancel($appointment)),
                default => $this->authorize('update', $appointment),
            };
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $appointment->refresh();

        $notesPayload = array_intersect_key($data, array_flip(['notes', 'appointment_date']));
        if ($notesPayload !== []) {
            $this->authorize('update', $appointment);
            $appointment->update($notesPayload);
        }

        $message = match ($status) {
            Appointment::STATUS_ACCEPTED => 'Rendez-vous accepté. Le patient a été notifié.',
            Appointment::STATUS_REJECTED => 'Rendez-vous refusé. Le patient a été notifié.',
            Appointment::STATUS_COMPLETED => 'Rendez-vous marqué comme terminé.',
            Appointment::STATUS_MISSED => 'Rendez-vous marqué comme non réalisé.',
            Appointment::STATUS_CANCELLED => 'Rendez-vous annulé.',
            default => 'Rendez-vous mis à jour avec succès.',
        };

        return redirect()->route('doctor.appointments.index')
            ->with('success', $message);
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('cancel', $appointment);

        try {
            $this->appointments->cancel($appointment);
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }

    /**
     * Annule un rendez-vous confirmé et reporte automatiquement le patient
     * sur le premier créneau disponible du même médecin.
     */
    public function reschedule(Appointment $appointment): RedirectResponse
    {
        $this->authorize('reschedule', $appointment);

        try {
            $newAppointment = $this->appointments->reschedule($appointment);
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $appointment->load(['patient', 'doctor.user', 'availability']);

        if ($newAppointment) {
            $newAppointment->load(['patient', 'doctor.user', 'availability']);

            $date = $newAppointment->appointment_date->format('d/m/Y');
            $time = $newAppointment->appointment_date->format('H:i');

            return redirect()->route('doctor.appointments.index')
                ->with('success', "Rendez-vous annulé et reporté automatiquement au $date à $time. Le patient a été notifié par email et notification.");
        }

        return redirect()->route('doctor.appointments.index')
            ->with('warning', 'Rendez-vous annulé. Aucun créneau disponible pour un report automatique. Le patient a été notifié.');
    }

    private function applyDoctorStatus(Appointment $appointment, string $ability, callable $action): void
    {
        $this->authorize($ability, $appointment);
        $action();
    }
}
