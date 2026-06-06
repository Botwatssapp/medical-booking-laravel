<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder pour la table doctors.
 *
 * Crée 20 profils médecins en réutilisant les spécialités
 * et les utilisateurs déjà présents en base (créés par SpecialitySeeder
 * et UserSeeder) pour éviter les conflits d'unicité.
 *
 * Correction : au lieu de `Doctor::factory(20)->create()` qui crée
 * 20 nouvelles Speciality (conflits avec SpecialitySeeder),
 * on assigne des IDs existants via `speciality_id`.
 *
 * Note : les 5 médecins aux identifiants fixes (jean@doctor.com, etc.)
 * créés dans UserSeeder n'ont pas encore de profil Doctor — ils peuvent
 * être complétés via l'interface admin ou en ajoutant une boucle ici.
 */
class DoctorSeeder extends Seeder
{
    /**
     * Peuple la table doctors en réutilisant les données existantes.
     *
     * @return void
     */
    public function run(): void
    {
        $specialityIds = Speciality::pluck('id');
        $doctorUserIds = User::doctors()
            ->doesntHave('doctor')
            ->pluck('id');

        if ($specialityIds->isEmpty()) {
            return;
        }

        // Crée 20 profils médecins en réutilisant les spécialités existantes.
        // Les utilisateurs de rôle 'doctor' sans profil sont prioritaires ;
        // si tous ont déjà un profil, la factory crée de nouveaux utilisateurs.
        foreach (range(1, 20) as $i) {
            $overrides = ['speciality_id' => $specialityIds->random()];

            if ($doctorUserIds->isNotEmpty()) {
                $overrides['user_id'] = $doctorUserIds->shift();
            }

            Doctor::factory()->create($overrides);
        }
    }
}
