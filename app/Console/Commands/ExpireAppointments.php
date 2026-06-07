<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentExpiredNotification;
use Illuminate\Console\Command;

/**
 * Commande planifiée : expire les rendez-vous dont la date est dépassée.
 *
 * Logique :
 *  - pending  + date passée → cancelled  (médecin n'a jamais confirmé)
 *  - accepted + date passée → missed     (confirmé mais non honoré)
 *
 * Le créneau de disponibilité des rendez-vous annulés (pending) est
 * remis à is_available=true pour ne pas bloquer les statistiques.
 * (Pour les accepted/missed, le créneau reste occupé — le RDV a eu lieu ou était prévu.)
 *
 * Un email de notification est envoyé au patient dans chaque cas.
 */
class ExpireAppointments extends Command
{
    protected $signature   = 'appointments:expire';
    protected $description = 'Expire les rendez-vous passés : pending → cancelled, accepted → missed';

    public function handle(): int
    {
        $now = now();

        // ── 1. pending → cancelled ────────────────────────────────────────────
        $pending = Appointment::pending()
            ->where('appointment_date', '<', $now)
            ->with(['patient', 'doctor.user', 'availability'])
            ->get();

        foreach ($pending as $apt) {
            $apt->update(['status' => Appointment::STATUS_CANCELLED]);

            // Libère le créneau (même passé) pour que les stats soient cohérentes
            $apt->availability?->update(['is_available' => true]);

            $this->sendMail($apt, 'cancelled');
        }

        // ── 2. accepted → missed ──────────────────────────────────────────────
        $accepted = Appointment::accepted()
            ->where('appointment_date', '<', $now)
            ->with(['patient', 'doctor.user', 'availability'])
            ->get();

        foreach ($accepted as $apt) {
            $apt->update(['status' => Appointment::STATUS_MISSED]);

            $this->sendMail($apt, 'missed');
        }

        $cancelledCount = $pending->count();
        $missedCount    = $accepted->count();
        $total          = $cancelledCount + $missedCount;

        $this->info("$total rendez-vous expiré(s) : $cancelledCount annulé(s), $missedCount manqué(s).");

        return self::SUCCESS;
    }

    private function sendMail(Appointment $apt, string $reason): void
    {
        try {
            // Email + notification base de données via le canal database
            $apt->patient->notify(new AppointmentExpiredNotification($apt, $reason));
        } catch (\Throwable) {
            $this->warn("Notification non envoyée pour le RDV #{$apt->id}");
        }
    }
}
