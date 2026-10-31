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
            ->assertOk()
            ->assertDontSee('4.9')
            ->assertDontSee('+12%');
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

    public function test_doctor_profile_edit_uses_real_fields_without_fake_claims(): void
    {
        $ctx = $this->validatedDoctor();
        $ctx['user']->update([
            'name' => 'ProfileDoc Unique',
            'email' => 'profiledoc.unique@example.com',
        ]);
        $ctx['doctor']->update([
            'phone' => '0612345678',
            'address' => 'Cabinet Profile Unique',
            'bio' => 'Bio Profile Unique',
        ]);
        $ctx['speciality']->update(['name' => 'Cardiologie Profile Unique']);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.profile.edit'))
            ->assertOk()
            ->assertSee('Mon profil praticien')
            ->assertSee('ProfileDoc Unique')
            ->assertSee('profiledoc.unique@example.com')
            ->assertSee('Cabinet Profile Unique')
            ->assertSee('Bio Profile Unique')
            ->assertSee('Cardiologie Profile Unique')
            ->assertSee('name="phone"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="bio"', false)
            ->assertSee('name="profile_image"', false)
            ->assertSee('Enregistrer les modifications')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('CNOM')
            ->assertDontSee('HDS')
            ->assertDontSee('RPPS')
            ->assertDontSee('4.9')
            ->assertDontSee('1,420')
            ->assertDontSee('14 ans')
            ->assertDontSee('AMO')
            ->assertDontSee('Secteur 1')
            ->assertDontSee('2FA')
            ->assertDontSee('CNDP')
            ->assertDontSee('Centre Cardio-Vasculaire')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('Pôle Vasculaire')
            ->assertDontSee('sarah.benali@santeconnect.ma')
            ->assertDontSee('Loi 09-08')
            ->assertDontSee('first_name')
            ->assertDontSee('consultation_fee');

        $this->actingAs($ctx['user'])
            ->patch(route('doctor.profile.update'), [
                'phone' => '0699887766',
                'address' => 'Nouveau Cabinet Unique',
                'bio' => 'Nouvelle bio unique',
            ])
            ->assertRedirect(route('doctor.profile.edit'));

        $ctx['doctor']->refresh();
        $this->assertSame('0699887766', $ctx['doctor']->phone);
        $this->assertSame('Nouveau Cabinet Unique', $ctx['doctor']->address);
        $this->assertSame('Nouvelle bio unique', $ctx['doctor']->bio);
        $this->assertSame($ctx['speciality']->id, $ctx['doctor']->speciality_id);
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

    public function test_doctor_availability_create_uses_real_fields_without_fake_claims(): void
    {
        $ctx = $this->validatedDoctor();
        $ctx['user']->update(['name' => 'AvailCreate Unique']);
        $ctx['doctor']->update(['address' => 'Cabinet Avail Unique']);
        $ctx['speciality']->update(['name' => 'Cardiologie Unique']);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.availabilities.create'))
            ->assertOk()
            ->assertSee('Ajouter une disponibilité')
            ->assertSee('Créneau unique')
            ->assertSee('Génération automatique')
            ->assertSee('Ajouter la disponibilité')
            ->assertSee('Configuration du créneau horaire')
            ->assertSee('Aperçu de la plage générée')
            ->assertSee('AvailCreate Unique')
            ->assertSee('Cabinet Avail Unique')
            ->assertSee('name="date"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('name="end_time"', false)
            ->assertSee('name="period_start"', false)
            ->assertSee('name="duration_minutes"', false)
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('RPPS')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('Doctolib')
            ->assertDontSee('Google Cal')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('day_of_week')
            ->assertDontSee('consultation_type')
            ->assertDontSee('is_recurring')
            ->assertDontSee('Enregistrer comme modèle')
            ->assertDontSee('+94%')
            ->assertDontSee('ECG')
            ->assertDontSee('24 Bd Bir Anzarane')
            ->assertDontSee('Loi 09-08');

        $date = now()->addDays(6)->toDateString();

        $this->actingAs($ctx['user'])
            ->post(route('doctor.availabilities.store'), [
                'mode' => 'single',
                'date' => $date,
                'start_time' => '09:00',
                'end_time' => '09:30',
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertDatabaseHas('availabilities', [
            'doctor_id' => $ctx['doctor']->id,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
        ]);

        $this->actingAs($ctx['user'])
            ->post(route('doctor.availabilities.store'), [
                'mode' => 'generate',
                'date' => now()->addDays(7)->toDateString(),
                'period_start' => '09:00',
                'period_end' => '10:00',
                'duration_minutes' => 30,
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertSame(3, Availability::query()->where('doctor_id', $ctx['doctor']->id)->count());
    }

    public function test_doctor_availabilities_index_uses_real_data_without_fake_claims(): void
    {
        $ctx = $this->validatedDoctor();
        $ctx['user']->update(['name' => 'AvailIndex Unique']);

        $freeDate = now()->addDays(2)->toDateString();
        $free = Availability::factory()->create([
            'doctor_id' => $ctx['doctor']->id,
            'date' => $freeDate,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'is_available' => true,
        ]);
        Availability::factory()->create([
            'doctor_id' => $ctx['doctor']->id,
            'date' => $freeDate,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'is_available' => false,
        ]);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.availabilities.index'))
            ->assertOk()
            ->assertSee('Mes disponibilités')
            ->assertSee('Ajouter une disponibilité')
            ->assertSee('09:00')
            ->assertSee('10:00')
            ->assertSee('Libre')
            ->assertSee('Réservé')
            ->assertSee('AvailIndex Unique')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('RPPS')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('Doctolib')
            ->assertDontSee('Google Cal')
            ->assertDontSee('Outlook')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('34h')
            ->assertDontSee('92%')
            ->assertDontSee('68 créneaux')
            ->assertDontSee('58 / sem.')
            ->assertDontSee('CHU')
            ->assertDontSee('1 sem. sur 2')
            ->assertDontSee('/doctor/availabilities/'.$free->id.'/edit')
            ->assertDontSee('Loi 09-08');

        $this->actingAs($ctx['user'])
            ->delete(route('doctor.availabilities.destroy', $free))
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertDatabaseMissing('availabilities', ['id' => $free->id]);
    }

    public function test_doctor_appointments_index_uses_real_data_without_fake_claims(): void
    {
        $ctx = $this->validatedDoctor();
        $todayPatient = User::factory()->patient()->create(['name' => 'Today Patient Unique']);
        $pendingPatient = User::factory()->patient()->create(['name' => 'Pending Patient Unique']);
        $donePatient = User::factory()->patient()->create(['name' => 'Done Patient Unique']);

        Appointment::factory()->accepted()->create([
            'patient_id' => $todayPatient->id,
            'doctor_id' => $ctx['doctor']->id,
            'appointment_date' => now(),
            'notes' => 'Motif Today Unique',
        ]);
        Appointment::factory()->pending()->create([
            'patient_id' => $pendingPatient->id,
            'doctor_id' => $ctx['doctor']->id,
            'appointment_date' => now()->addDays(2),
            'notes' => 'Motif Pending Unique',
        ]);
        Appointment::factory()->completed()->create([
            'patient_id' => $donePatient->id,
            'doctor_id' => $ctx['doctor']->id,
            'appointment_date' => now()->subDays(3),
        ]);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.appointments.index'))
            ->assertOk()
            ->assertSee('Mes rendez-vous')
            ->assertSee('Today Patient Unique')
            ->assertSee('Pending Patient Unique')
            ->assertSee('Done Patient Unique')
            ->assertSee('Motif Today Unique')
            ->assertSee('Accepter le RDV')
            ->assertSee('Refuser')
            ->assertSee('name="status"', false)
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Mehdi Alami')
            ->assertDontSee('#PT-89402')
            ->assertDontSee('Fatima Zahra Idrissi')
            ->assertDontSee('Youssef Chraibi')
            ->assertDontSee('Salma Berrada')
            ->assertDontSee('Tariq Mansouri')
            ->assertDontSee('Cabinet 2A')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('Palpitations')
            ->assertDontSee('Holter')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('RPPS')
            ->assertDontSee('138/85')
            ->assertDontSee('Pénicilline')
            ->assertDontSee('ECG')
            ->assertDontSee('BE 492019')
            ->assertDontSee('SMS')
            ->assertDontSee('Groupe sanguin')
            ->assertDontSee('IMC');

        $this->actingAs($ctx['user'])
            ->get(route('doctor.appointments.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Pending Patient Unique')
            ->assertDontSee('Today Patient Unique')
            ->assertDontSee('Done Patient Unique');

        $this->actingAs($ctx['user'])
            ->get(route('doctor.appointments.index', ['search' => 'Pending Patient']))
            ->assertOk()
            ->assertSee('Pending Patient Unique')
            ->assertDontSee('Today Patient Unique');

        $this->actingAs($ctx['user'])
            ->get(route('doctor.appointments.index', ['status' => 'today']))
            ->assertOk()
            ->assertSee('Today Patient Unique')
            ->assertDontSee('Pending Patient Unique');
    }

    public function test_dashboard_empty_states_do_not_crash(): void
    {
        $ctx = $this->validatedDoctor();

        $this->actingAs($ctx['user'])
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Aucun rendez-vous à venir')
            ->assertSee('Aucun créneau libre')
            ->assertDontSee('4.9')
            ->assertDontSee('+12%');
    }

    public function test_dashboard_week_and_month_views_list_real_appointments(): void
    {
        $ctx = $this->validatedDoctor();
        $patient = User::factory()->patient()->create(['name' => 'AgendaWeek Patient Unique']);

        Appointment::factory()->pending()->create([
            'doctor_id' => $ctx['doctor']->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->setTime(10, 0),
        ]);

        $this->actingAs($ctx['user'])
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Planning clinique du jour')
            ->assertDontSee('Vue semaine non disponible')
            ->assertDontSee('Vue mois non disponible');

        $this->actingAs($ctx['user'])
            ->get(route('doctor.dashboard', ['view' => 'week']))
            ->assertOk()
            ->assertSee('Planning clinique de la semaine')
            ->assertSee('AgendaWeek Patient Unique')
            ->assertDontSee('Vue semaine non disponible');

        $this->actingAs($ctx['user'])
            ->get(route('doctor.dashboard', ['view' => 'month']))
            ->assertOk()
            ->assertSee('Planning clinique du mois')
            ->assertSee('AgendaWeek Patient Unique')
            ->assertDontSee('Vue mois non disponible');
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
