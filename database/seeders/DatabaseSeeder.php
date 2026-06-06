<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal orchestrant tous les seeders de l'application.
 *
 * Ordre d'exécution respectant les contraintes de clés étrangères :
 * 1. Spécialités (référentiel)
 * 2. Utilisateurs (base des rôles)
 * 3. Médecins (dépend des utilisateurs et spécialités)
 * 4. Disponibilités (dépend des médecins)
 * 5. Rendez-vous (dépend de tout ce qui précède)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Peuple la base de données dans l'ordre des dépendances.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            SpecialitySeeder::class,
            UserSeeder::class,
            DoctorSeeder::class,
            AvailabilitySeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
