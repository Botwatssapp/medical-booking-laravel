<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registration_works(): void
    {
        $response = $this->post('/register', [
            'name' => 'Patient Test',
            'email' => 'patient-foundation@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'patient',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'patient-foundation@example.com',
            'role' => 'patient',
        ]);
        $this->assertDatabaseMissing('doctors', [
            'user_id' => User::query()->where('email', 'patient-foundation@example.com')->value('id'),
        ]);
    }

    public function test_doctor_registration_works(): void
    {
        $response = $this->post('/register', [
            'name' => 'Doctor Test',
            'email' => 'doctor-foundation@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'doctor',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('doctor.dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'doctor-foundation@example.com',
            'role' => 'doctor',
        ]);
        $this->assertDatabaseMissing('doctors', [
            'user_id' => User::query()->where('email', 'doctor-foundation@example.com')->value('id'),
        ]);
    }

    public function test_admin_registration_is_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Admin Test',
            'email' => 'admin-foundation@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'admin-foundation@example.com',
        ]);
    }

    public function test_invalid_role_registration_is_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Invalid Role',
            'email' => 'invalid-role@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'superadmin',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
    }

    public function test_login_redirects_according_to_role(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $admin = User::factory()->admin()->create();

        $this->post('/login', [
            'email' => $patient->email,
            'password' => 'password',
        ])->assertRedirect(route('patient.dashboard', absolute: false));

        $this->post('/logout');

        $this->post('/login', [
            'email' => $doctor->email,
            'password' => 'password',
        ])->assertRedirect(route('doctor.dashboard', absolute: false));

        $this->post('/logout');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_unverified_factory_state_works(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_email_verification_redirects_to_role_dashboard(): void
    {
        $user = User::factory()->unverified()->patient()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('patient.dashboard', absolute: false).'?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_patient_cannot_access_another_patients_appointment(): void
    {
        $owner = User::factory()->patient()->create();
        $intruder = User::factory()->patient()->create();
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $owner->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('patient.appointments.show', $appointment))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('patient.appointments.update', $appointment), [
                'notes' => 'Intrusion',
            ])
            ->assertForbidden();
    }

    public function test_patient_appointment_date_cannot_be_changed_via_edit(): void
    {
        $patient = User::factory()->patient()->create();
        $originalDate = now()->addDays(4)->setTime(10, 0);
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'appointment_date' => $originalDate,
            'notes' => 'Avant',
        ]);

        $this->actingAs($patient)
            ->put(route('patient.appointments.update', $appointment), [
                'appointment_date' => now()->addDays(10)->format('Y-m-d\TH:i'),
                'notes' => 'Après',
            ])
            ->assertRedirect(route('patient.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertSame('Après', $appointment->notes);
        $this->assertTrue($appointment->appointment_date->equalTo($originalDate));
    }

    public function test_doctor_cannot_access_another_doctors_appointment(): void
    {
        $ownerUser = User::factory()->doctor()->create();
        $ownerDoctor = Doctor::factory()->create(['user_id' => $ownerUser->id]);
        $otherUser = User::factory()->doctor()->create();
        Doctor::factory()->create(['user_id' => $otherUser->id]);

        $appointment = Appointment::factory()->pending()->create([
            'doctor_id' => $ownerDoctor->id,
        ]);

        $this->actingAs($otherUser)
            ->get(route('doctor.appointments.show', $appointment))
            ->assertForbidden();
    }

    public function test_unvalidated_doctor_cannot_access_operational_features(): void
    {
        $doctor = User::factory()->doctor()->create();

        $this->actingAs($doctor)
            ->get(route('doctor.availabilities.index'))
            ->assertForbidden();

        $this->actingAs($doctor)
            ->get(route('doctor.appointments.index'))
            ->assertForbidden();
    }

    public function test_patient_cannot_access_admin_routes(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($patient)
            ->get(route('admin.appointments.index'))
            ->assertForbidden();
    }

    public function test_doctor_cannot_access_admin_routes(): void
    {
        $doctor = User::factory()->doctor()->create();

        $this->actingAs($doctor)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('patient.dashboard'))->assertRedirect(route('login'));
        $this->get(route('doctor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_search_does_not_bypass_status_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $matchingPatient = User::factory()->patient()->create(['name' => 'ZyxwSearch Patient']);
        $otherPatient = User::factory()->patient()->create(['name' => 'QvbnOther Patient']);
        $matchingDoctorUser = User::factory()->doctor()->create(['name' => 'ZyxwSearch Doctor']);
        $otherDoctorUser = User::factory()->doctor()->create(['name' => 'QvbnOther Doctor']);
        $matchingDoctor = Doctor::factory()->create(['user_id' => $matchingDoctorUser->id]);
        $otherDoctor = Doctor::factory()->create(['user_id' => $otherDoctorUser->id]);

        Appointment::factory()->pending()->create([
            'patient_id' => $matchingPatient->id,
            'doctor_id' => $otherDoctor->id,
        ]);

        Appointment::factory()->accepted()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $matchingDoctor->id,
        ]);

        Appointment::factory()->pending()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.appointments.index', [
            'search' => 'ZyxwSearch',
            'status' => 'pending',
        ]));

        $response->assertOk();
        $response->assertSee('ZyxwSearch Patient');
        $response->assertDontSee('ZyxwSearch Doctor');
        $response->assertDontSee('QvbnOther Patient');
    }

    public function test_admin_search_without_status_still_matches_patient_and_doctor(): void
    {
        $admin = User::factory()->admin()->create();
        $matchingPatient = User::factory()->patient()->create(['name' => 'ZyxwOnly Patient']);
        $matchingDoctorUser = User::factory()->doctor()->create(['name' => 'ZyxwOnly Doctor']);
        $otherPatient = User::factory()->patient()->create(['name' => 'Nomatch Patient']);
        $unrelatedPatient = User::factory()->patient()->create(['name' => 'Unrelated Patient']);
        $matchingDoctor = Doctor::factory()->create(['user_id' => $matchingDoctorUser->id]);
        $otherDoctor = Doctor::factory()->create(['user_id' => User::factory()->doctor()->create(['name' => 'Nomatch Doctor'])->id]);

        Appointment::factory()->pending()->create([
            'patient_id' => $matchingPatient->id,
            'doctor_id' => $otherDoctor->id,
        ]);

        Appointment::factory()->accepted()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $matchingDoctor->id,
        ]);

        Appointment::factory()->pending()->create([
            'patient_id' => $unrelatedPatient->id,
            'doctor_id' => $otherDoctor->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.appointments.index', [
            'search' => 'ZyxwOnly',
        ]));

        $response->assertOk();
        $response->assertSee('ZyxwOnly Patient');
        $response->assertSee('ZyxwOnly Doctor');
        $response->assertDontSee('Unrelated Patient');
    }

    public function test_admin_index_without_filters_lists_appointments(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create(['name' => 'Listed Patient']);
        Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.appointments.index'))
            ->assertOk()
            ->assertSee('Listed Patient');
    }

    public function test_patient_cannot_manage_availability(): void
    {
        $patient = User::factory()->patient()->create();
        $slot = Availability::factory()->create();

        $this->actingAs($patient)
            ->get(route('doctor.availabilities.index'))
            ->assertForbidden();

        $this->actingAs($patient)
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertForbidden();
    }
}
