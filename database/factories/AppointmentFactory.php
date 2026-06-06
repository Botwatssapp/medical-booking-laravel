<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Appointment.
 *
 * Génère des rendez-vous de test avec les statuts appropriés
 * selon que la date est passée ou future.
 *
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * Les rendez-vous en cours (futurs) ne peuvent pas avoir les statuts
     * 'completed' ou 'missed' — ces statuts sont réservés aux rendez-vous passés.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointmentDate = fake()->dateTimeBetween('now', '+30 days');

        return [
            'patient_id'       => User::factory()->patient(),
            'doctor_id'        => Doctor::factory(),
            'availability_id'  => Availability::factory(),
            'appointment_date' => $appointmentDate,
            // Statuts cohérents pour des rendez-vous futurs
            'status'           => fake()->randomElement([
                Appointment::STATUS_PENDING,
                Appointment::STATUS_ACCEPTED,
                Appointment::STATUS_REJECTED,
                Appointment::STATUS_CANCELLED,
            ]),
            'notes'            => fake()->optional()->paragraph(),
        ];
    }

    /**
     * État : rendez-vous en attente.
     *
     * @return static
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    /**
     * État : rendez-vous accepté.
     *
     * @return static
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_ACCEPTED,
        ]);
    }

    /**
     * État : rendez-vous rejeté.
     *
     * @return static
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_REJECTED,
        ]);
    }

    /**
     * État : rendez-vous annulé.
     *
     * @return static
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    /**
     * État : rendez-vous terminé (passé).
     *
     * @return static
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'           => Appointment::STATUS_COMPLETED,
            'appointment_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * État : rendez-vous manqué (passé).
     *
     * @return static
     */
    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'           => Appointment::STATUS_MISSED,
            'appointment_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
