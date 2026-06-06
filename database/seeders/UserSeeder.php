<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Administrateur',
            'email'    => 'admin@medical.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Patients
        $patients = [
            ['name' => 'Marc Lefebvre',   'email' => 'marc@patient.com'],
            ['name' => 'Sophie Martin',   'email' => 'sophie@patient.com'],
            ['name' => 'Ahmed Benali',    'email' => 'ahmed@patient.com'],
            ['name' => 'Marie Dupont',    'email' => 'marie@patient.com'],
        ];

        foreach ($patients as $patient) {
            User::create([
                'name'     => $patient['name'],
                'email'    => $patient['email'],
                'password' => Hash::make('password'),
                'role'     => 'patient',
            ]);
        }

        // Doctors
        $doctors = [
            ['name' => 'Jean Dupont',     'email' => 'jean@doctor.com'],
            ['name' => 'Sarah Mansouri',  'email' => 'sarah@doctor.com'],
            ['name' => 'Thomas Meyer',    'email' => 'thomas@doctor.com'],
            ['name' => 'Leila Amrani',    'email' => 'leila@doctor.com'],
            ['name' => 'Pierre Bernard',  'email' => 'pierre@doctor.com'],
        ];

        foreach ($doctors as $doctor) {
            User::create([
                'name'     => $doctor['name'],
                'email'    => $doctor['email'],
                'password' => Hash::make('password'),
                'role'     => 'doctor',
            ]);
        }
    }
}
