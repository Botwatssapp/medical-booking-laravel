<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de confirmation d'un rendez-vous médical.
 *
 * Envoyé au patient lorsque le médecin accepte son rendez-vous.
 * L'appointment est passé en propriété publique pour être accessible
 * directement dans la vue Blade sans variable supplémentaire.
 */
class AppointmentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crée une nouvelle instance du mailable.
     *
     * @param  Appointment $appointment  Le rendez-vous confirmé
     */
    public function __construct(public readonly Appointment $appointment)
    {
    }

    /**
     * Définit l'enveloppe de l'email (sujet, expéditeur, destinataires).
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rendez-vous confirmé — SantéConnect',
        );
    }

    /**
     * Définit le contenu de l'email.
     *
     * La vue `emails.appointment-confirmed` a accès à `$appointment`
     * via la propriété publique du mailable.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-confirmed',
        );
    }
}
