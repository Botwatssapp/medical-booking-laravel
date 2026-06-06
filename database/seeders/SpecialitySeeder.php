<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    public function run(): void
    {
        $specialities = [
            ['name' => 'Cardiologie',        'description' => 'Surveillance et soins cardiaques préventifs complets.'],
            ['name' => 'Dermatologie',        'description' => 'Soins de la peau, des cheveux et des ongles.'],
            ['name' => 'Pédiatrie',           'description' => 'Soins médicaux dédiés aux enfants et adolescents.'],
            ['name' => 'Neurologie',          'description' => 'Diagnostic et traitement des maladies du système nerveux.'],
            ['name' => 'Orthopédie',          'description' => 'Traitement des maladies et traumatismes de l\'appareil locomoteur.'],
            ['name' => 'Médecine Générale',   'description' => 'Soins de santé primaires pour tous les âges.'],
            ['name' => 'Gynécologie',         'description' => 'Santé de la femme et suivi gynécologique.'],
            ['name' => 'Ophtalmologie',       'description' => 'Soins et chirurgie des yeux.'],
            ['name' => 'Psychologie',         'description' => 'Accompagnement bienveillant pour votre santé mentale.'],
            ['name' => 'Radiologie',          'description' => 'Imagerie médicale et diagnostics de précision.'],
        ];

        foreach ($specialities as $speciality) {
            Speciality::create($speciality);
        }
    }
}
