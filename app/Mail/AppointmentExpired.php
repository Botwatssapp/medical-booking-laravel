<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé automatiquement quand un rendez-vous expire.
 *
 * Deux cas :
 *  - reason = 'cancelled' : le médecin n'a jamais confirmé et la date est passée
 *  - reason = 'missed'    : le rendez-vous était confirmé mais la date est passée sans complétion
 */
class AppointmentExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string      $reason,   // 'cancelled' | 'missed'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->reason === 'missed'
            ? 'Rendez-vous manqué — SantéConnect'
            : 'Rendez-vous expiré — SantéConnect';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appointment-expired');
    }
}
