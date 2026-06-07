<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Mail\AppointmentExpired;
use App\Models\Appointment;
use App\Models\Availability;
use App\Notifications\AppointmentExpiredNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = auth()->user()->doctor;

        $query = $doctor->appointments()->with(['patient', 'availability']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->paginate(15)->withQueryString();

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
        $this->authorize('update', $appointment);

        $data = $request->validated();

        // Libérer le créneau si le médecin annule manuellement
        if (($data['status'] ?? null) === Appointment::STATUS_CANCELLED) {
            DB::transaction(function () use ($appointment, $data) {
                $appointment->availability?->update(['is_available' => true]);
                $appointment->update($data);
            });
        } else {
            $appointment->update($data);
        }

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        DB::transaction(function () use ($appointment) {
            $appointment->availability?->update(['is_available' => true]);
            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        });

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }

    /**
     * Annule un rendez-vous confirmé et reporte automatiquement le patient
     * sur le premier créneau disponible du même médecin.
     *
     * Si aucun créneau n'est disponible, le rendez-vous est simplement
     * annulé et le patient reçoit un email d'annulation.
     */
    public function reschedule(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        if ($appointment->status !== Appointment::STATUS_ACCEPTED) {
            return back()->with('error', 'Seuls les rendez-vous confirmés peuvent être reportés.');
        }

        $newAppointment = null;

        DB::transaction(function () use ($appointment, &$newAppointment) {
            // 1 — Libérer l'ancien créneau
            if ($appointment->availability_id) {
                Availability::where('id', $appointment->availability_id)
                    ->lockForUpdate()
                    ->first()
                    ?->update(['is_available' => true]);
            }

            // 2 — Annuler l'ancien rendez-vous
            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);

            // 3 — Chercher le prochain créneau libre du même médecin
            $nextSlot = Availability::where('doctor_id', $appointment->doctor_id)
                ->where('is_available', true)
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->orderBy('start_time')
                ->lockForUpdate()
                ->first();

            if (!$nextSlot) {
                return; // Pas de créneau → juste annulation
            }

            // 4 — Réserver le nouveau créneau
            $nextSlot->update(['is_available' => false]);

            // 5 — Créer le nouveau rendez-vous (auto-confirmé)
            $newAppointment = Appointment::create([
                'patient_id'       => $appointment->patient_id,
                'doctor_id'        => $appointment->doctor_id,
                'availability_id'  => $nextSlot->id,
                'appointment_date' => $nextSlot->date->format('Y-m-d') . ' ' . $nextSlot->start_time,
                'status'           => Appointment::STATUS_ACCEPTED,
                'notes'            => $appointment->notes,
            ]);
        });

        // — Notifications (hors transaction pour ne pas bloquer en cas d'erreur SMTP) —
        $appointment->load(['patient', 'doctor.user', 'availability']);

        if ($newAppointment) {
            $newAppointment->load(['patient', 'doctor.user', 'availability']);

            try {
                // Email + notification base de données en un seul appel
                $appointment->patient->notify(
                    new AppointmentRescheduledNotification($appointment, $newAppointment)
                );
            } catch (\Throwable) {
                // Silencieux : le report a bien eu lieu même si la notification échoue
            }

            $date = $newAppointment->appointment_date->format('d/m/Y');
            $time = $newAppointment->appointment_date->format('H:i');

            return redirect()->route('doctor.appointments.index')
                ->with('success', "Rendez-vous annulé et reporté automatiquement au $date à $time. Le patient a été notifié par email et notification.");
        }

        // Aucun créneau disponible → annulation simple + notification
        try {
            $appointment->patient->notify(
                new AppointmentExpiredNotification($appointment, 'cancelled')
            );
        } catch (\Throwable) {}

        return redirect()->route('doctor.appointments.index')
            ->with('warning', 'Rendez-vous annulé. Aucun créneau disponible pour un report automatique. Le patient a été notifié.');
    }
}
