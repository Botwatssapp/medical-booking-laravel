<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SpecialitySeeder::class,
            UserSeeder::class,
            DoctorSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
