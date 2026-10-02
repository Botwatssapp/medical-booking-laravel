<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_doctor_can_access_dashboard(): void
    {
        $doctorUser = User::factory()->doctor()->create();
        Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $this->actingAs($doctorUser)
            ->get(route('doctor.dashboard'))
            ->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_doctor_routes(): void
    {
        $this->get(route('doctor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('doctor.appointments.index'))->assertRedirect(route('login'));
        $this->get(route('doctor.availabilities.index'))->assertRedirect(route('login'));
    }

    public function test_patient_cannot_access_doctor_operational_routes(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('doctor.dashboard'))
            ->assertForbidden();

        $this->actingAs($patient)
            ->get(route('doctor.appointments.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_use_doctor_operational_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('doctor.appointments.index'))
            ->assertForbidden();
    }

    public function test_unvalidated_doctor_cannot_access_availability_or_appointments(): void
    {
        $pending = User::factory()->doctor()->create();

        $this->actingAs($pending)
            ->get(route('doctor.availabilities.index'))
            ->assertForbidden();

        $this->actingAs($pending)
            ->get(route('doctor.appointments.index'))
            ->assertForbidden();

        $this->actingAs($pending)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Profil en attente de validation');
    }

    public function test_doctor_cannot_access_another_doctors_appointment(): void
    {
        $owner = $this->validatedDoctor();
        $other = $this->validatedDoctor();
        $appointment = Appointment::factory()->pending()->create([
            'doctor_id' => $owner['doctor']->id,
        ]);

        $this->actingAs($other['user'])
            ->get(route('doctor.appointments.show', $appointment))
            ->assertForbidden();

        $this->actingAs($other['user'])
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_doctor_cannot_delete_another_doctors_availability(): void
    {
        $owner = $this->validatedDoctor();
        $other = $this->validatedDoctor();
        $slot = Availability::factory()->create([
            'doctor_id' => $owner['doctor']->id,
            'is_available' => true,
        ]);

        $this->actingAs($other['user'])
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertForbidden();

        $this->assertDatabaseHas('availabilities', ['id' => $slot->id]);
    }

    public function test_forged_doctor_id_does_not_create_availability_for_another_doctor(): void
    {
        $owner = $this->validatedDoctor();
        $other = $this->validatedDoctor();

        $this->actingAs($owner['user'])
            ->post(route('doctor.availabilities.store'), [
                'mode' => 'single',
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
                'doctor_id' => $other['doctor']->id,
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $owner['doctor']->id,
            'start_time' => '10:00:00',
        ]);
        $this->assertDatabaseMissing('availabilities', [
            'doctor_id' => $other['doctor']->id,
            'start_time' => '10:00:00',
        ]);
    }

    public function test_occupied_availability_cannot_be_deleted(): void
    {
        $ctx = $this->validatedDoctor();
        $slot = Availability::factory()->create([
            'doctor_id' => $ctx['doctor']->id,
            'is_available' => false,
        ]);
        Appointment::factory()->pending()->create([
            'doctor_id' => $ctx['doctor']->id,
            'availability_id' => $slot->id,
        ]);

        $this->actingAs($ctx['user'])
            ->from(route('doctor.availabilities.index'))
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertRedirect(route('doctor.availabilities.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('availabilities', ['id' => $slot->id]);
    }

    public function test_invalid_and_overlapping_availability_are_rejected(): void
    {
        $ctx = $this->validatedDoctor();

        $this->actingAs($ctx['user'])
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(4)->toDateString(),
                'start_time' => '11:00',
                'end_time' => '10:00',
            ])
            ->assertSessionHasErrors('end_time');

        Availability::factory()->create([
            'doctor_id' => $ctx['doctor']->id,
            'date' => now()->addDays(5)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'is_available' => true,
        ]);

        $this->actingAs($ctx['user'])
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(5)->toDateString(),
                'start_time' => '09:30',
                'end_time' => '10:30',
            ])
            ->assertSessionHasErrors();
    }

    public function test_pending_appointment_can_be_accepted_and_rejected(): void
    {
        $ctx = $this->validatedDoctor();
        $patient = User::factory()->patient()->create();
        $slot = Availability::factory()->create([
            'doctor_id' => $ctx['doctor']->id,
            'is_available' => false,
        ]);
        $accepted = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $ctx['doctor']->id,
            'availability_id' => $slot->id,
        ]);

        $this->actingAs($ctx['user'])
            ->patch(route('doctor.appointments.update', $accepted), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertRedirect(route('doctor.appointments.index'))
            ->assertSessionHas('success');

        $this->assertSame(Appointment::STATUS_ACCEPTED, $accepted->fresh()->status);

        $rejected = Appointment::factory()->pending()->create([
            'patient_id' => User::factory()->patient()->create()->id,
            'doctor_id' => $ctx['doctor']->id,
        ]);

        $this->actingAs($ctx['user'])
            ->patch(route('doctor.appointments.update', $rejected), [
                'status' => Appointment::STATUS_REJECTED,
            ])
            ->assertRedirect(route('doctor.appointments.index'));

        $this->assertSame(Appointment::STATUS_REJECTED, $rejected->fresh()->status);
    }

    public function test_invalid_appointment_transition_is_rejected(): void
    {
        $ctx = $this->validatedDoctor();
        $appointment = Appointment::factory()->pending()->create([
            'doctor_id' => $ctx['doctor']->id,
        ]);

        $this->actingAs($ctx['user'])
            ->from(route('doctor.appointments.show', $appointment))
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_COMPLETED,
            ])
            ->assertRedirect(route('doctor.appointments.show', $appointment))
            ->assertSessionHas('error');

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_doctor_cannot_change_speciality_or_ownership_via_profile(): void
    {
        $ctx = $this->validatedDoctor();
        $otherSpeciality = Speciality::factory()->create();

        $this->actingAs($ctx['user'])
            ->patch(route('doctor.profile.update'), [
                'phone' => '0600000099',
                'speciality_id' => $otherSpeciality->id,
                'user_id' => 999,
            ])
            ->assertRedirect(route('doctor.profile.edit'));

        $ctx['doctor']->refresh();
        $this->assertSame($ctx['speciality']->id, $ctx['doctor']->speciality_id);
        $this->assertSame($ctx['user']->id, $ctx['doctor']->user_id);
        $this->assertSame('0600000099', $ctx['doctor']->phone);
    }

    public function test_appointment_details_do_not_expose_private_patient_medical_fields(): void
    {
        $ctx = $this->validatedDoctor();
        $patient = User::factory()->patient()->create([
            'name' => 'Visible Patient',
            'blood_type' => 'AB+',
            'weight' => 72.5,
            'height' => 178,
            'emergency_contact' => 'Secret Contact',
        ]);
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $ctx['doctor']->id,
            'notes' => 'Motif consultation',
        ]);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Visible Patient')
            ->assertSee('Motif consultation')
            ->assertDontSee('AB+')
            ->assertDontSee('Secret Contact')
            ->assertDontSee('72.5')
            ->assertDontSee('Groupe sanguin')
            ->assertDontSee('IMC');
    }

    public function test_dashboard_empty_states_do_not_crash(): void
    {
        $ctx = $this->validatedDoctor();

        $this->actingAs($ctx['user'])
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Aucun rendez-vous à venir')
            ->assertSee('Aucun créneau libre');
    }

    /**
     * @return array{user: User, doctor: Doctor, speciality: Speciality}
     */
    private function validatedDoctor(): array
    {
        $user = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [
            'user' => $user,
            'doctor' => $doctor,
            'speciality' => $doctor->speciality,
        ];
    }
}
