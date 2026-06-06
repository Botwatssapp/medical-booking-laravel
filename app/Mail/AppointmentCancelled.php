<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email d'annulation d'un rendez-vous médical.
 *
 * Envoyé au patient lorsque le rendez-vous est annulé
 * (par lui-même, par le médecin ou par l'administrateur).
 */
class AppointmentCancelled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crée une nouvelle instance du mailable.
     *
     * @param  Appointment $appointment  Le rendez-vous annulé
     */
    public function __construct(public readonly Appointment $appointment)
    {
    }

    /**
     * Définit l'enveloppe de l'email.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rendez-vous annulé — SantéConnect',
        );
    }

    /**
     * Définit le contenu de l'email.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-cancelled',
        );
    }
}
