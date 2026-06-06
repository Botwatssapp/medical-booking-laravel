<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = User::where('role', 'patient')->get();
        $doctors  = Doctor::all();

        $appointments = [
            [
                'patient' => 'marc@patient.com',
                'doctor'  => 'jean@doctor.com',
                'date'    => now()->addDays(2)->format('Y-m-d'),
                'time'    => '09:00',
                'reason'  => 'Consultation cardiologique de routine',
                'status'  => 'confirmed',
            ],
            [
                'patient' => 'marc@patient.com',
                'doctor'  => 'sarah@doctor.com',
                'date'    => now()->addDays(5)->format('Y-m-d'),
                'time'    => '14:30',
                'reason'  => 'Consultation dermatologique',
                'status'  => 'pending',
            ],
            [
                'patient' => 'sophie@patient.com',
                'doctor'  => 'thomas@doctor.com',
                'date'    => now()->addDays(1)->format('Y-m-d'),
                'time'    => '10:00',
                'reason'  => 'Bilan de santé annuel',
                'status'  => 'confirmed',
            ],
            [
                'patient' => 'ahmed@patient.com',
                'doctor'  => 'pierre@doctor.com',
                'date'    => now()->subDays(3)->format('Y-m-d'),
                'time'    => '11:30',
                'reason'  => 'Douleur au genou',
                'status'  => 'cancelled',
            ],
            [
                'patient' => 'marie@patient.com',
                'doctor'  => 'leila@doctor.com',
                'date'    => now()->addDays(7)->format('Y-m-d'),
                'time'    => '15:00',
                'reason'  => 'Consultation pédiatrique',
                'status'  => 'pending',
            ],
        ];

        foreach ($appointments as $data) {
            $patient = User::where('email', $data['patient'])->first();
            $doctor  = User::where('email', $data['doctor'])->first()->doctor;

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id'  => $doctor->id,
                'date'       => $data['date'],
                'time_slot'  => $data['time'],
                'reason'     => $data['reason'],
                'status'     => $data['status'],
            ]);
        }
    }
}
