<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;

/**
 * Seeder pour la table specialities.
 *
 * Insère les spécialités médicales de référence.
 * Utilise `firstOrCreate` pour être idempotent : exécutable plusieurs fois
 * sans générer de violation de contrainte unique.
 */
class SpecialitySeeder extends Seeder
{
    /**
     * Peuple la table specialities avec les spécialités médicales de base.
     *
     * @return void
     */
    public function run(): void
    {
        $specialities = [
            ['name' => 'Cardiologie',      'description' => 'Spécialiste des maladies du cœur et des vaisseaux sanguins.'],
            ['name' => 'Dermatologie',     'description' => 'Spécialiste de la peau, des cheveux et des ongles.'],
            ['name' => 'Pédiatrie',        'description' => 'Médecin spécialisé dans les soins des enfants.'],
            ['name' => 'Neurologie',       'description' => 'Spécialiste du système nerveux et des maladies neurologiques.'],
            ['name' => 'Ophtalmologie',    'description' => 'Spécialiste des yeux et de la vision.'],
            ['name' => 'Gynécologie',      'description' => 'Médecin spécialisé dans la santé de la femme.'],
            ['name' => 'Psychiatrie',      'description' => 'Spécialiste de la santé mentale et des troubles psychiatriques.'],
            ['name' => 'Dentiste',         'description' => 'Professionnel des soins dentaires et de la santé buccale.'],
        ];

        foreach ($specialities as $speciality) {
            // firstOrCreate : ignore silencieusement les doublons (idempotent)
            Speciality::firstOrCreate(
                ['name' => $speciality['name']],
                ['description' => $speciality['description']]
            );
        }
    }
}
