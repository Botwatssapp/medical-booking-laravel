<?php

namespace App\Notifications;

use App\Mail\AppointmentExpired as ExpiredMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string      $reason, // 'cancelled' | 'missed'
    ) {}

    public function via(object $_notifiable): array
    {
        return ['database', 'mail'];   // database d'abord
    }

    public function toDatabase(object $_notifiable): array
    {
        $date = $this->appointment->appointment_date->format('d/m/Y');
        $time = $this->appointment->availability
            ? substr($this->appointment->availability->start_time, 0, 5)
            : $this->appointment->appointment_date->format('H:i');

        if ($this->reason === 'missed') {
            $title   = 'Rendez-vous manqué';
            $message = "Votre rendez-vous du $date à $time avec Dr. {$this->appointment->doctor->user->name} a été marqué comme manqué.";
            $icon    = 'running_with_errors';
            $color   = 'orange';
        } else {
            $title   = 'Rendez-vous annulé automatiquement';
            $message = "Votre demande du $date à $time avec Dr. {$this->appointment->doctor->user->name} a expiré (non confirmée à temps).";
            $icon    = 'event_busy';
            $color   = 'red';
        }

        return [
            'type'           => 'appointment_' . $this->reason,
            'icon'           => $icon,
            'color'          => $color,
            'title'          => $title,
            'message'        => $message,
            'date'           => $date,
            'time'           => $time,
            'doctor_name'    => $this->appointment->doctor->user->name,
            'appointment_id' => $this->appointment->id,
            'url'            => route('patient.appointments.index'),
        ];
    }

    public function toMail(object $notifiable): ExpiredMail
    {
        return (new ExpiredMail($this->appointment, $this->reason))
            ->to($notifiable->email, $notifiable->name);
    }
}
