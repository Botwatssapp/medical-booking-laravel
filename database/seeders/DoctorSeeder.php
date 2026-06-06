<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            [
                'email'         => 'jean@doctor.com',
                'speciality_id' => 1, // Cardiologie
                'phone'         => '06 12 34 56 78',
                'address'       => 'Hôpital Saint-Louis, Paris',
                'bio'           => 'Cardiologue avec 15 ans d\'expérience spécialisé dans les maladies cardiovasculaires.',
            ],
            [
                'email'         => 'sarah@doctor.com',
                'speciality_id' => 2, // Dermatologie
                'phone'         => '06 23 45 67 89',
                'address'       => 'Clinique du Parc, Lyon',
                'bio'           => 'Dermatologue experte en dermatologie esthétique et traitement des maladies de peau.',
            ],
            [
                'email'         => 'thomas@doctor.com',
                'speciality_id' => 6, // Médecine Générale
                'phone'         => '06 34 56 78 90',
                'address'       => 'Cabinet Médical, Marseille',
                'bio'           => 'Médecin généraliste disponible pour toutes consultations médicales.',
            ],
            [
                'email'         => 'leila@doctor.com',
                'speciality_id' => 3, // Pédiatrie
                'phone'         => '06 45 67 89 01',
                'address'       => 'Centre de Santé, Bordeaux',
                'bio'           => 'Pédiatre dédiée à la santé des enfants de 0 à 18 ans.',
            ],
            [
                'email'         => 'pierre@doctor.com',
                'speciality_id' => 5, // Orthopédie
                'phone'         => '06 56 78 90 12',
                'address'       => 'Hôpital Universitaire, Toulouse',
                'bio'           => 'Chirurgien orthopédiste spécialisé dans les traumatismes sportifs.',
            ],
        ];

        foreach ($doctors as $data) {
            $user = User::where('email', $data['email'])->first();
            Doctor::create([
                'user_id'       => $user->id,
                'speciality_id' => $data['speciality_id'],
                'phone'         => $data['phone'],
                'address'       => $data['address'],
                'bio'           => $data['bio'],
                'is_available'  => true,
            ]);
        }
    }
}
