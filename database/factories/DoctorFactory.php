<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Doctor.
 *
 * Crée un profil médecin complet avec :
 * - Un utilisateur lié de rôle 'doctor'
 * - Une spécialité médicale
 * - Des informations de contact (téléphone, adresse, biographie)
 *
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory()->doctor(),
            'speciality_id' => Speciality::factory(),
            'phone'         => fake()->phoneNumber(),
            'address'       => fake()->address(),
            'bio'           => fake()->paragraph(),
            'photo'         => null,
        ];
    }
}
