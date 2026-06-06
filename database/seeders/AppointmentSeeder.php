<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder pour la table appointments.
 *
 * Crée 200 rendez-vous en réutilisant les patients, médecins
 * et créneaux déjà présents en base pour éviter la création
 * en cascade de nouveaux enregistrements via la chaîne de factory.
 *
 * Correction : au lieu de `Appointment::factory(200)->create()`
 * qui crée 200 nouveaux User + Doctor + Availability + Speciality,
 * on distribue les rendez-vous sur les données existantes.
 */
class AppointmentSeeder extends Seeder
{
    /**
     * Peuple la table appointments avec des rendez-vous de test.
     *
     * @return void
     */
    public function run(): void
    {
        $patientIds      = User::patients()->pluck('id');
        $doctorIds       = Doctor::pluck('id');
        $availabilityIds = Availability::pluck('id');

        if ($patientIds->isEmpty() || $doctorIds->isEmpty()) {
            return;
        }

        // Crée 200 rendez-vous en réutilisant les données existantes
        foreach (range(1, 200) as $i) {
            $doctorId       = $doctorIds->random();
            $availabilityId = $availabilityIds->isNotEmpty()
                ? $availabilityIds->random()
                : null;

            Appointment::factory()->create([
                'patient_id'      => $patientIds->random(),
                'doctor_id'       => $doctorId,
                'availability_id' => $availabilityId,
            ]);
        }
    }
}
