<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Availability.
 *
 * Génère des créneaux de disponibilité cohérents :
 * - La date est dans les 30 prochains jours
 * - end_time est toujours APRÈS start_time (correction bug)
 *
 * @extends Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * Correction : end_time est calculé en ajoutant 1 heure à start_time
     * pour garantir que end_time > start_time.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Heure de début : entre 8h et 17h
        $startHour = fake()->numberBetween(8, 17);
        $startTime = sprintf('%02d:00', $startHour);

        // Heure de fin : toujours 1 heure après l'heure de début
        $endHour = $startHour + 1;
        $endTime = sprintf('%02d:00', $endHour);

        return [
            'doctor_id'    => Doctor::factory(),
            'date'         => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'is_available' => true,
        ];
    }
}
