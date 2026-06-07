<?php

namespace App\Notifications;

use App\Mail\AppointmentRescheduled as RescheduledMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Appointment $oldAppointment,
        public readonly Appointment $newAppointment,
    ) {}

    public function via(object $_notifiable): array
    {
        return ['database', 'mail'];   // database d'abord — indépendant du mail
    }

    /**
     * Canal database — toujours exécuté, même si le mail échoue.
     */
    public function toDatabase(object $_notifiable): array
    {
        $oldDate = $this->oldAppointment->appointment_date->format('d/m/Y');
        $oldTime = $this->oldAppointment->availability
            ? substr($this->oldAppointment->availability->start_time, 0, 5)
            : $this->oldAppointment->appointment_date->format('H:i');

        $newDate = $this->newAppointment->appointment_date->format('d/m/Y');
        $newTime = $this->newAppointment->availability
            ? substr($this->newAppointment->availability->start_time, 0, 5)
            : $this->newAppointment->appointment_date->format('H:i');

        return [
            'type'           => 'appointment_rescheduled',
            'icon'           => 'event_repeat',
            'color'          => 'amber',
            'title'          => 'Rendez-vous reporté',
            'message'        => "Votre rendez-vous du $oldDate à $oldTime a été reporté au $newDate à $newTime.",
            'old_date'       => $oldDate,
            'old_time'       => $oldTime,
            'new_date'       => $newDate,
            'new_time'       => $newTime,
            'doctor_name'    => $this->newAppointment->doctor->user->name,
            'appointment_id' => $this->newAppointment->id,
            'url'            => route('patient.appointments.index'),
        ];
    }

    /**
     * Canal mail — on passe le destinataire explicitement via ->to().
     */
    public function toMail(object $notifiable): RescheduledMail
    {
        return (new RescheduledMail($this->oldAppointment, $this->newAppointment))
            ->to($notifiable->email, $notifiable->name);
    }
}
