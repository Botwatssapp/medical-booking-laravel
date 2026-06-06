<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

/**
 * Seeder pour la table availabilities.
 *
 * Génère des créneaux horaires de façon déterministe pour chaque médecin
 * afin d'éviter les violations de la contrainte unique
 * (doctor_id, date, start_time, end_time).
 *
 * Logique : pour chaque médecin, crée des créneaux pour les 30 prochains jours,
 * de 8h à 17h, par tranches d'1 heure. `firstOrCreate` ignore les doublons.
 *
 * Volume : 20 médecins × 30 jours × 9 créneaux = 5 400 créneaux maximum.
 * On limite à ~500 créneaux en sélectionnant seulement les premiers jours.
 */
class AvailabilitySeeder extends Seeder
{
    /**
     * Peuple la table availabilities de façon déterministe sans doublons.
     *
     * @return void
     */
    public function run(): void
    {
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            return;
        }

        $count = 0;
        $targetCount = 500;

        foreach ($doctors as $doctor) {
            if ($count >= $targetCount) {
                break;
            }

            // 30 jours de disponibilités par médecin
            for ($day = 1; $day <= 30 && $count < $targetCount; $day++) {
                $date = now()->addDays($day)->format('Y-m-d');

                // Créneaux horaires de 8h à 17h (9 créneaux d'1h)
                for ($hour = 8; $hour < 17 && $count < $targetCount; $hour++) {
                    $startTime = sprintf('%02d:00', $hour);
                    $endTime   = sprintf('%02d:00', $hour + 1);

                    // firstOrCreate ignore les doublons sans lever d'exception
                    Availability::firstOrCreate(
                        [
                            'doctor_id'  => $doctor->id,
                            'date'       => $date,
                            'start_time' => $startTime,
                            'end_time'   => $endTime,
                        ],
                        ['is_available' => true]
                    );

                    $count++;
                }
            }
        }
    }
}
