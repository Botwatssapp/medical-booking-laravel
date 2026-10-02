<?php

namespace App\Notifications;

use App\Mail\AppointmentRefused as RefusedMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentRefusedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    public function via(object $_notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $_notifiable): array
    {
        $date = $this->appointment->appointment_date->format('d/m/Y');
        $time = $this->appointment->availability
            ? substr($this->appointment->availability->start_time, 0, 5)
            : $this->appointment->appointment_date->format('H:i');

        return [
            'type' => 'appointment_rejected',
            'icon' => 'cancel',
            'color' => 'red',
            'title' => 'Rendez-vous refusé',
            'message' => "Votre rendez-vous du $date à $time avec Dr. {$this->appointment->doctor->user->name} a été refusé.",
            'date' => $date,
            'time' => $time,
            'doctor_name' => $this->appointment->doctor->user->name,
            'appointment_id' => $this->appointment->id,
            'url' => route('patient.doctors.index'),
        ];
    }

    public function toMail(object $notifiable): RefusedMail
    {
        return (new RefusedMail($this->appointment))
            ->to($notifiable->email, $notifiable->name);
    }
}
