<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder pour la table users.
 *
 * Utilise `firstOrCreate` pour être idempotent : les comptes fixes
 * (admin, médecins de test) ne sont créés que s'ils n'existent pas encore.
 * Les 50 patients factory sont ajoutés à chaque appel (données de volume).
 */
class UserSeeder extends Seeder
{
    /**
     * Peuple la table users avec des données de test.
     *
     * @return void
     */
    public function run(): void
    {
        // Compte administrateur (idempotent)
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Administrateur',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // 50 patients via factory (données aléatoires — emails uniques garantis par Faker)
        User::factory(50)->patient()->create();

        // 5 médecins avec identifiants fixes pour les tests (idempotent)
        $doctors = [
            ['name' => 'Jean Dupont',    'email' => 'jean@doctor.com'],
            ['name' => 'Sarah Mansouri', 'email' => 'sarah@doctor.com'],
            ['name' => 'Thomas Meyer',   'email' => 'thomas@doctor.com'],
            ['name' => 'Leila Amrani',   'email' => 'leila@doctor.com'],
            ['name' => 'Pierre Bernard', 'email' => 'pierre@doctor.com'],
        ];

        foreach ($doctors as $doctor) {
            User::firstOrCreate(
                ['email' => $doctor['email']],
                [
                    'name'     => $doctor['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'doctor',
                ]
            );
        }
    }
}
