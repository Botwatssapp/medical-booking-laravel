<?php

namespace App\Console\Commands;

use App\Exceptions\AppointmentException;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Console\Command;

/**
 * Commande planifiée : expire les rendez-vous dont la date est dépassée.
 *
 *  - pending  + date passée → cancelled  (créneau libéré si plus d'occupant)
 *  - accepted + date passée → missed     (créneau reste occupé)
 *
 * La logique métier et les notifications sont déléguées à AppointmentService.
 */
class ExpireAppointments extends Command
{
    protected $signature = 'appointments:expire';

    protected $description = 'Expire les rendez-vous passés : pending → cancelled, accepted → missed';

    public function __construct(private readonly AppointmentService $appointments)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();

        $pending = Appointment::pending()
            ->where('appointment_date', '<', $now)
            ->with(['patient', 'doctor.user', 'availability'])
            ->get();

        $accepted = Appointment::accepted()
            ->where('appointment_date', '<', $now)
            ->with(['patient', 'doctor.user', 'availability'])
            ->get();

        $cancelledCount = 0;
        $missedCount = 0;

        foreach ($pending as $apt) {
            try {
                $this->appointments->expirePending($apt);
                $cancelledCount++;
            } catch (AppointmentException $e) {
                $this->warn("RDV #{$apt->id} non expiré : {$e->getMessage()}");
            }
        }

        foreach ($accepted as $apt) {
            try {
                $this->appointments->expireAccepted($apt);
                $missedCount++;
            } catch (AppointmentException $e) {
                $this->warn("RDV #{$apt->id} non expiré : {$e->getMessage()}");
            }
        }

        $total = $cancelledCount + $missedCount;

        $this->info("$total rendez-vous expiré(s) : $cancelledCount annulé(s), $missedCount manqué(s).");

        return self::SUCCESS;
    }
}
