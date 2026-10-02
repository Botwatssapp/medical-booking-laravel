<?php

namespace Tests\Feature;

use App\Exceptions\DoctorException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\AvailabilityService;
use App\Services\DoctorOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_registration_creates_user_without_doctor_profile(): void
    {
        $this->post('/register', $this->registrationPayload([
            'email' => 'doc@example.com',
            'role' => 'doctor',
        ]))
            ->assertRedirect(route('doctor.dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::where('email', 'doc@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('doctor', $user->role);
        $this->assertFalse($user->hasDoctorProfile());
        $this->assertSame(0, Doctor::count());

        $this->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Profil en attente de validation');
    }

    public function test_patient_registration_still_works_and_creates_no_doctor(): void
    {
        $this->post('/register', $this->registrationPayload([
            'email' => 'patient@example.com',
            'role' => 'patient',
        ]))
            ->assertRedirect(route('patient.dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertSame('patient', User::where('email', 'patient@example.com')->value('role'));
        $this->assertSame(0, Doctor::count());
    }

    public function test_role_cannot_be_escalated_to_admin_via_registration(): void
    {
        $this->post('/register', $this->registrationPayload([
            'email' => 'hacker@example.com',
            'role' => 'admin',
        ]))->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
    }

    public function test_admin_can_confirm_pending_doctor_profile(): void
    {
        ['admin' => $admin, 'pendingUser' => $pending, 'speciality' => $speciality] = $this->pendingContext();

        $this->actingAs($admin)
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => $speciality->id,
                'phone' => '0600000000',
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $this->assertDatabaseHas('doctors', [
            'user_id' => $pending->id,
            'speciality_id' => $speciality->id,
            'phone' => '0600000000',
        ]);

        $this->assertTrue($pending->fresh()->hasDoctorProfile());
    }

    public function test_duplicate_doctor_profile_is_rejected(): void
    {
        ['admin' => $admin, 'pendingUser' => $pending, 'speciality' => $speciality] = $this->pendingContext();
        $this->app->make(DoctorOnboardingService::class)
            ->createProfile($pending, $speciality->id);

        $this->actingAs($admin)
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => $speciality->id,
            ])
            ->assertRedirect(route('admin.doctors.create'))
            ->assertSessionHasErrors('user_id');

        $this->assertSame(1, Doctor::where('user_id', $pending->id)->count());
    }

    public function test_service_prevents_duplicate_doctor_records(): void
    {
        ['pendingUser' => $pending, 'speciality' => $speciality] = $this->pendingContext();
        $service = $this->app->make(DoctorOnboardingService::class);
        $service->createProfile($pending, $speciality->id);

        $this->expectException(DoctorException::class);
        $service->createProfile($pending, $speciality->id);
    }

    public function test_patient_cannot_validate_a_doctor(): void
    {
        $patient = User::factory()->patient()->create();
        ['pendingUser' => $pending, 'speciality' => $speciality] = $this->pendingContext();

        $this->actingAs($patient)
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => $speciality->id,
            ])
            ->assertForbidden();

        $this->assertSame(0, Doctor::count());
    }

    public function test_doctor_cannot_self_validate(): void
    {
        ['pendingUser' => $pending, 'speciality' => $speciality] = $this->pendingContext();

        $this->actingAs($pending)
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => $speciality->id,
            ])
            ->assertForbidden();

        $this->assertFalse($pending->fresh()->hasDoctorProfile());
    }

    public function test_repeated_admin_validation_is_rejected_safely(): void
    {
        ['admin' => $admin, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'speciality' => $speciality] = $this->validatedContext();

        $this->actingAs($admin)
            ->post(route('admin.doctors.store'), [
                'user_id' => $doctorUser->id,
                'speciality_id' => $speciality->id,
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertSame(1, Doctor::where('user_id', $doctorUser->id)->count());
        $this->assertTrue($doctor->fresh()->exists);
    }

    public function test_unvalidated_doctor_cannot_create_availability(): void
    {
        $pending = User::factory()->doctor()->create();

        $this->actingAs($pending)
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertForbidden();

        $this->assertSame(0, Availability::count());
    }

    public function test_unvalidated_doctor_cannot_list_appointments(): void
    {
        $pending = User::factory()->doctor()->create();

        $this->actingAs($pending)
            ->get(route('doctor.appointments.index'))
            ->assertForbidden();
    }

    public function test_unvalidated_doctor_can_access_dashboard_and_own_profile(): void
    {
        $pending = User::factory()->doctor()->create();

        $this->actingAs($pending)
            ->get(route('doctor.dashboard'))
            ->assertOk();

        $this->actingAs($pending)
            ->get(route('doctor.profile.edit'))
            ->assertOk();
    }

    public function test_doctor_can_edit_own_profile_but_not_speciality(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor, 'speciality' => $speciality] = $this->validatedContext();
        $otherSpeciality = Speciality::factory()->create();

        $this->actingAs($doctorUser)
            ->patch(route('doctor.profile.update'), [
                'phone' => '0611111111',
                'address' => '12 rue de la Santé',
                'bio' => 'Cardiologue',
                'speciality_id' => $otherSpeciality->id,
                'user_id' => 999,
            ])
            ->assertRedirect(route('doctor.profile.edit'));

        $doctor->refresh();
        $this->assertSame('0611111111', $doctor->phone);
        $this->assertSame($speciality->id, $doctor->speciality_id);
        $this->assertSame($doctorUser->id, $doctor->user_id);
    }

    public function test_doctor_cannot_edit_another_doctors_profile(): void
    {
        $a = $this->validatedContext();
        $b = $this->validatedContext();

        $this->actingAs($a['doctorUser'])
            ->put(route('admin.doctors.update', $b['doctor']), [
                'phone' => 'hijacked',
                'speciality_id' => $b['speciality']->id,
            ])
            ->assertForbidden();

        $this->actingAs($a['doctorUser'])
            ->patch(route('doctor.profile.update'), [
                'phone' => '0600000001',
            ])
            ->assertRedirect(route('doctor.profile.edit'));

        $this->assertNull($b['doctor']->fresh()->phone);
        $this->assertSame('0600000001', $a['doctor']->fresh()->phone);
    }

    public function test_patient_cannot_edit_doctor_profile(): void
    {
        $patient = User::factory()->patient()->create();
        ['doctor' => $doctor] = $this->validatedContext();

        $this->actingAs($patient)
            ->patch(route('doctor.profile.update'), ['phone' => '0600000000'])
            ->assertForbidden();

        $this->actingAs($patient)
            ->put(route('admin.doctors.update', $doctor), ['phone' => '0600000000'])
            ->assertForbidden();

        $this->assertNull($doctor->fresh()->phone);
    }

    public function test_admin_can_update_doctor_speciality(): void
    {
        ['admin' => $admin, 'doctor' => $doctor] = $this->validatedContext();
        $other = Speciality::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.doctors.update', $doctor), [
                'speciality_id' => $other->id,
                'phone' => '0622222222',
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $this->assertSame($other->id, $doctor->fresh()->speciality_id);
        $this->assertSame('0622222222', $doctor->fresh()->phone);
    }

    public function test_invalid_speciality_is_rejected(): void
    {
        ['admin' => $admin, 'pendingUser' => $pending] = $this->pendingContext();

        $this->actingAs($admin)
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => 99999,
            ])
            ->assertRedirect(route('admin.doctors.create'))
            ->assertSessionHasErrors('speciality_id');

        $this->assertSame(0, Doctor::count());
    }

    public function test_forged_patient_user_id_cannot_become_a_doctor_profile(): void
    {
        ['admin' => $admin, 'speciality' => $speciality] = $this->pendingContext();
        $patient = User::factory()->patient()->create();

        $this->actingAs($admin)
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [
                'user_id' => $patient->id,
                'speciality_id' => $speciality->id,
            ])
            ->assertRedirect(route('admin.doctors.create'))
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('doctors', ['user_id' => $patient->id]);
    }

    public function test_unauthenticated_user_cannot_access_doctor_management(): void
    {
        ['doctor' => $doctor] = $this->validatedContext();

        $this->get(route('admin.doctors.index'))->assertRedirect(route('login'));
        $this->post(route('admin.doctors.store'), [])->assertRedirect(route('login'));
        $this->put(route('admin.doctors.update', $doctor), [])->assertRedirect(route('login'));
        $this->get(route('doctor.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_forged_user_id_on_admin_update_does_not_reassign_ownership(): void
    {
        ['admin' => $admin, 'doctor' => $doctor, 'doctorUser' => $owner] = $this->validatedContext();
        $other = User::factory()->doctor()->create();

        $this->actingAs($admin)
            ->put(route('admin.doctors.update', $doctor), [
                'user_id' => $other->id,
                'phone' => '0633333333',
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $this->assertSame($owner->id, $doctor->fresh()->user_id);
        $this->assertSame('0633333333', $doctor->fresh()->phone);
    }

    public function test_validated_doctor_works_with_availability_service(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->validatedContext();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($doctorUser)
            ->post(route('doctor.availabilities.store'), [
                'date' => $date,
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $slot = $this->app->make(AvailabilityService::class)
            ->create($doctor, $date, '11:00', '11:30');

        $this->assertSame($doctor->id, $slot->doctor_id);
        $this->assertTrue($slot->is_available);
    }

    public function test_appointment_service_behavior_remains_unchanged(): void
    {
        ['doctor' => $doctor] = $this->validatedContext();
        $patient = User::factory()->patient()->create();
        $slot = Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'is_available' => true,
        ]);

        $appointment = $this->app->make(AppointmentService::class)
            ->create($patient, $doctor->id, $slot->id);

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_doctor_a_cannot_create_availability_for_doctor_b(): void
    {
        $a = $this->validatedContext();
        $b = $this->validatedContext();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($a['doctorUser'])
            ->post(route('doctor.availabilities.store'), [
                'doctor_id' => $b['doctor']->id,
                'date' => $date,
                'start_time' => '09:00',
                'end_time' => '09:30',
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertTrue(
            Availability::where('doctor_id', $a['doctor']->id)->whereDate('date', $date)->exists()
        );
        $this->assertFalse(
            Availability::where('doctor_id', $b['doctor']->id)->whereDate('date', $date)->exists()
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'patient',
        ], $overrides);
    }

    /**
     * @return array{admin: User, pendingUser: User, speciality: Speciality}
     */
    private function pendingContext(): array
    {
        return [
            'admin' => User::factory()->admin()->create(),
            'pendingUser' => User::factory()->doctor()->create(),
            'speciality' => Speciality::factory()->create(),
        ];
    }

    /**
     * @return array{admin: User, doctorUser: User, doctor: Doctor, speciality: Speciality}
     */
    private function validatedContext(): array
    {
        $admin = User::factory()->admin()->create();
        $doctorUser = User::factory()->doctor()->create();
        $speciality = Speciality::factory()->create();
        $doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'speciality_id' => $speciality->id,
            'phone' => null,
        ]);

        return compact('admin', 'doctorUser', 'doctor', 'speciality');
    }
}
