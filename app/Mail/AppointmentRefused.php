<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de refus d'un rendez-vous médical.
 *
 * Envoyé au patient lorsque le médecin refuse son rendez-vous.
 * Le statut du rendez-vous passe à 'rejected' dans la base de données.
 */
class AppointmentRefused extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crée une nouvelle instance du mailable.
     *
     * @param  Appointment $appointment  Le rendez-vous refusé
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
            subject: 'Rendez-vous refusé — SantéConnect',
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
            view: 'emails.appointment-refused',
        );
    }
}
