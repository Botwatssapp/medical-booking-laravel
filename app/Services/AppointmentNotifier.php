<?php

namespace App\Services;

use App\Models\Appointment;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\AppointmentExpiredNotification;
use App\Notifications\AppointmentRefusedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Envoie les notifications de rendez-vous APRÈS le commit métier.
 *
 * N'effectue aucune transition de statut et n'écrit pas dans
 * availabilities. Un échec d'envoi est journalisé, jamais relancé
 * dans la transaction d'AppointmentService.
 *
 * Synchrone volontairement : MAIL_MAILER=log / array, pas de queue:work.
 */
class AppointmentNotifier
{
    public function notifyAccepted(Appointment $appointment): void
    {
        $this->notifyPatient($appointment, new AppointmentConfirmedNotification($appointment));
    }

    public function notifyRejected(Appointment $appointment): void
    {
        $this->notifyPatient($appointment, new AppointmentRefusedNotification($appointment));
    }

    public function notifyCancelled(Appointment $appointment): void
    {
        $this->notifyPatient($appointment, new AppointmentCancelledNotification($appointment));
    }

    public function notifyExpired(Appointment $appointment, string $reason): void
    {
        $this->notifyPatient($appointment, new AppointmentExpiredNotification($appointment, $reason));
    }

    public function notifyRescheduled(Appointment $oldAppointment, Appointment $newAppointment): void
    {
        $this->notifyPatient(
            $newAppointment,
            new AppointmentRescheduledNotification($oldAppointment, $newAppointment),
        );
    }

    /**
     * Notifications admin : uniquement si le statut a réellement changé
     * et qu'un mailable existe pour la transition.
     */
    public function notifyAdminTransition(string $oldStatus, Appointment $appointment): void
    {
        if ($oldStatus === $appointment->status) {
            return;
        }

        match ($appointment->status) {
            Appointment::STATUS_ACCEPTED => $this->notifyAccepted($appointment),
            Appointment::STATUS_REJECTED => $this->notifyRejected($appointment),
            Appointment::STATUS_CANCELLED => $this->notifyCancelled($appointment),
            default => null,
        };
    }

    private function notifyPatient(Appointment $appointment, Notification $notification): void
    {
        $appointment->loadMissing(['patient', 'doctor.user', 'doctor.speciality', 'availability']);

        $patient = $appointment->patient;

        if (! $patient || blank($patient->email)) {
            Log::warning('Notification rendez-vous ignorée : patient ou email manquant.', [
                'appointment_id' => $appointment->id,
            ]);

            return;
        }

        try {
            $patient->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('Échec d\'envoi de notification rendez-vous.', [
                'appointment_id' => $appointment->id,
                'notification' => $notification::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
