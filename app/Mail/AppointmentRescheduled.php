<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé au patient quand le médecin reporte son rendez-vous.
 *
 * Contient les détails de l'ancien rendez-vous (annulé) et du nouveau
 * rendez-vous (automatiquement confirmé sur le premier créneau libre).
 */
class AppointmentRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $oldAppointment,
        public readonly Appointment $newAppointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre rendez-vous a été reporté — SantéConnect',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appointment-rescheduled');
    }
}
