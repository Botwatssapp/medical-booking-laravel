<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_patient_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->patient()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_doctor_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->doctor()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_dashboard_contains_real_data(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create(['name' => 'DashPatient Unique']);
        $pending = User::factory()->doctor()->create(['name' => 'DashPending Doc']);
        $doctorUser = User::factory()->doctor()->create(['name' => 'DashActive Doc']);
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('DashPatient Unique')
            ->assertSee('DashPending Doc')
            ->assertSee('DashActive Doc')
            ->assertDontSee('10,000')
            ->assertDontSee('99%');
    }

    public function test_unauthorized_user_cannot_access_user_management(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.users.create'))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_manage_doctors(): void
    {
        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $this->actingAs($doctorUser)->get(route('admin.doctors.index'))->assertForbidden();
        $this->actingAs($doctorUser)->get(route('admin.doctors.edit', $doctor))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_manage_specialities(): void
    {
        $specialty = Speciality::factory()->create();
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)->get(route('admin.specialties.index'))->assertForbidden();
        $this->actingAs($patient)
            ->post(route('admin.specialties.store'), ['name' => 'Hacked'])
            ->assertForbidden();
        $this->actingAs($patient)
            ->delete(route('admin.specialties.destroy', $specialty))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_manage_appointments(): void
    {
        $appointment = Appointment::factory()->pending()->create();
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)->get(route('admin.appointments.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.appointments.show', $appointment))->assertForbidden();
        $this->actingAs($patient)
            ->post(route('admin.appointments.cancel', $appointment))
            ->assertForbidden();
    }

    public function test_forged_user_id_cannot_validate_unintended_account(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create();
        $speciality = Speciality::factory()->create();

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

    public function test_invalid_speciality_is_rejected_on_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->doctor()->create();

        $this->actingAs($admin)
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => 99999,
            ])
            ->assertRedirect(route('admin.doctors.create'))
            ->assertSessionHasErrors('speciality_id');
    }

    public function test_admin_appointment_search_by_email_does_not_bypass_status_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = User::factory()->patient()->create([
            'name' => 'EmailMatch Patient',
            'email' => 'emailmatch.unique@example.com',
        ]);
        $other = User::factory()->patient()->create(['name' => 'OtherListed Patient']);
        $doctor = Doctor::factory()->create();

        Appointment::factory()->pending()->create([
            'patient_id' => $matching->id,
            'doctor_id' => $doctor->id,
        ]);
        Appointment::factory()->accepted()->create([
            'patient_id' => $matching->id,
            'doctor_id' => $doctor->id,
        ]);
        Appointment::factory()->pending()->create([
            'patient_id' => $other->id,
            'doctor_id' => $doctor->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.appointments.index', [
                'search' => 'emailmatch.unique',
                'status' => 'pending',
            ]))
            ->assertOk()
            ->assertSee('EmailMatch Patient')
            ->assertDontSee('OtherListed Patient');
    }

    public function test_admin_cannot_create_invalid_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Bad Role',
                'email' => 'badrole@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'superadmin',
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'badrole@example.com']);
    }

    public function test_appointment_management_preserves_service_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $completed = Appointment::factory()->completed()->create();

        $this->actingAs($admin)
            ->from(route('admin.appointments.show', $completed))
            ->post(route('admin.appointments.cancel', $completed))
            ->assertRedirect(route('admin.appointments.show', $completed))
            ->assertSessionHas('error');

        $this->assertSame(Appointment::STATUS_COMPLETED, $completed->fresh()->status);

        $this->actingAs($admin)
            ->from(route('admin.appointments.show', $completed))
            ->patch(route('admin.appointments.updateStatus', $completed), [
                'status' => 'not-a-status',
            ])
            ->assertRedirect(route('admin.appointments.show', $completed))
            ->assertSessionHasErrors('status');
    }

    public function test_admin_views_do_not_expose_passwords_or_private_medical_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create([
            'name' => 'Privacy Patient',
            'blood_type' => 'AB+',
            'weight' => 72.5,
            'height' => 178,
            'emergency_contact' => 'Secret Contact',
            'remember_token' => 'AdminTokenSecret99',
        ]);
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'notes' => 'Motif consultation admin',
        ]);

        $show = $this->actingAs($admin)->get(route('admin.appointments.show', $appointment));
        $show->assertOk()
            ->assertSee('Privacy Patient')
            ->assertSee('Motif consultation admin')
            ->assertDontSee('AB+')
            ->assertDontSee('Secret Contact')
            ->assertDontSee('AdminTokenSecret99')
            ->assertDontSee($patient->password);

        $users = $this->actingAs($admin)->get(route('admin.users.edit', $patient));
        $users->assertOk()
            ->assertDontSee('AdminTokenSecret99')
            ->assertDontSee($patient->password);
    }

    public function test_admin_empty_states_do_not_crash(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.appointments.index'))
            ->assertOk()
            ->assertSee('Aucun rendez-vous');
        $this->actingAs($admin)->get(route('admin.specialties.index'))
            ->assertOk()
            ->assertSee('Aucune spécialité');
    }
}
