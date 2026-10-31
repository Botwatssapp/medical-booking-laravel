<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patientIds = User::patients()->pluck('id');
        $doctorIds = Doctor::pluck('id');

        if ($patientIds->isEmpty() || $doctorIds->isEmpty()) {
            return;
        }

        // Each availability can only be occupied by one appointment.
        $availabilityIds = Availability::query()
            ->where('is_available', true)
            ->pluck('id')
            ->shuffle();

        $appointmentCount = min(200, $availabilityIds->count());

        foreach ($availabilityIds->take($appointmentCount) as $availabilityId) {
            $availability = Availability::find($availabilityId);

            if (!$availability) {
                continue;
            }

            Appointment::factory()->create([
                'patient_id'      => $patientIds->random(),
                'doctor_id'       => $availability->doctor_id,
                'availability_id' => $availability->id,
            ]);
        }
    }
}