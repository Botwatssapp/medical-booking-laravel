<?php

namespace Database\Factories;

use App\Models\Speciality;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Speciality.
 *
 * Utilise une liste prédéfinie de spécialités médicales réelles
 * pour éviter les doublons et les erreurs d'épuisement du pool Faker
 * (qui survenaient avec `fake()->unique()->word()` lors de la création
 * de nombreux enregistrements en cascade via les factories associées).
 *
 * @extends Factory<Speciality>
 */
class SpecialityFactory extends Factory
{
    /**
     * Liste de spécialités médicales réelles utilisées comme pool de noms.
     *
     * @var list<string>
     */
    private static array $specialties = [
        'Cardiologie',
        'Pédiatrie',
        'Dermatologie',
        'Neurologie',
        'Orthopédie',
        'Gynécologie',
        'Ophtalmologie',
        'ORL',
        'Psychiatrie',
        'Rhumatologie',
        'Gastro-entérologie',
        'Urologie',
        'Endocrinologie',
        'Pneumologie',
        'Oncologie',
        'Radiologie',
        'Anesthésiologie',
        'Chirurgie générale',
        'Médecine générale',
        'Infectiologie',
        'Hématologie',
        'Néphrologie',
        'Médecine interne',
        'Chirurgie cardiaque',
        'Neurochirurgie',
        'Chirurgie plastique',
        'Médecine du sport',
        'Médecine du travail',
        'Allergologie',
        'Gériatrie',
    ];

    /**
     * Définit l'état par défaut du modèle.
     *
     * Correction : remplacement de `fake()->unique()->word()` (pool trop limité)
     * par un nom tiré d'une liste de spécialités médicales réelles.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->randomElement(self::$specialties),
            'description' => fake()->paragraph(),
        ];
    }
}
