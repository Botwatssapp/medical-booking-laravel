<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_dashboard_requires_authentication(): void
    {
        $this->get(route('patient.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_patient_sees_only_their_own_appointments_on_dashboard_and_index(): void
    {
        $patient = User::factory()->patient()->create();
        $other = User::factory()->patient()->create();
        $own = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(3),
        ]);
        $own->doctor->user->update(['name' => 'OwnClinic Physician']);
        $foreign = Appointment::factory()->pending()->create([
            'patient_id' => $other->id,
            'appointment_date' => now()->addDays(4),
        ]);
        $foreign->doctor->user->update(['name' => 'ForeignClinic Physician']);

        $this->actingAs($patient)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Dr. '.$own->doctor->user->name)
            ->assertDontSee('Dr. '.$foreign->doctor->user->name)
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('4.9')
            ->assertDontSee('SC-8924')
            ->assertDontSee('350 MAD')
            ->assertDontSee('CNSS')
            ->assertDontSee('HIPAA');

        $this->actingAs($patient)
            ->get(route('patient.appointments.index'))
            ->assertOk()
            ->assertSee('Dr. '.$own->doctor->user->name)
            ->assertDontSee('Dr. '.$foreign->doctor->user->name);
    }

    public function test_patient_cannot_view_or_cancel_another_patients_appointment(): void
    {
        $owner = User::factory()->patient()->create();
        $intruder = User::factory()->patient()->create();
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $owner->id,
            'appointment_date' => now()->addDays(2),
        ]);

        $this->actingAs($intruder)
            ->get(route('patient.appointments.show', $appointment))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('patient.appointments.destroy', $appointment))
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_unvalidated_doctor_does_not_appear_as_bookable(): void
    {
        $patient = User::factory()->patient()->create();
        $pending = User::factory()->doctor()->create(['name' => 'PendingGhost Doctor']);
        $validated = Doctor::factory()->create();
        $validated->user->update(['name' => 'Visible Clinic Doctor']);

        $this->actingAs($patient)
            ->get(route('patient.doctors.index'))
            ->assertOk()
            ->assertSee('Visible Clinic Doctor')
            ->assertDontSee('PendingGhost Doctor');

        $this->assertFalse($pending->hasDoctorProfile());
    }

    public function test_validated_doctor_appears_in_directory(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = Doctor::factory()->create();
        $doctor->user->update(['name' => 'Directory Doctor']);

        $this->actingAs($patient)
            ->get(route('patient.doctors.index'))
            ->assertOk()
            ->assertSee('Directory Doctor')
            ->assertSee($doctor->speciality->name)
            ->assertSee('Annuaire des spécialistes')
            ->assertDontSee('4.9')
            ->assertDontSee('128 Doctors')
            ->assertDontSee('450 MAD')
            ->assertDontSee('CNSS')
            ->assertDontSee('CNOPS')
            ->assertDontSee('Highest Rated');
    }

    public function test_doctor_search_and_speciality_filter_work(): void
    {
        $patient = User::factory()->patient()->create();
        $cardio = Speciality::factory()->create(['name' => 'Cardiologie']);
        $dermato = Speciality::factory()->create(['name' => 'Dermatologie']);

        $cardioDoctor = Doctor::factory()->create(['speciality_id' => $cardio->id]);
        $cardioDoctor->user->update(['name' => 'UniqueCardio Name']);

        $dermatoDoctor = Doctor::factory()->create(['speciality_id' => $dermato->id]);
        $dermatoDoctor->user->update(['name' => 'UniqueDermato Name']);

        $this->actingAs($patient)
            ->get(route('patient.doctors.index', ['search' => 'UniqueCardio']))
            ->assertOk()
            ->assertSee('UniqueCardio Name')
            ->assertDontSee('UniqueDermato Name');

        $this->actingAs($patient)
            ->get(route('patient.doctors.index', ['speciality' => $dermato->id]))
            ->assertOk()
            ->assertSee('UniqueDermato Name')
            ->assertDontSee('UniqueCardio Name');
    }

    public function test_doctor_details_belong_to_the_requested_doctor(): void
    {
        $patient = User::factory()->patient()->create();
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $doctorA->user->update(['name' => 'Doctor Alpha']);
        $doctorB->user->update(['name' => 'Doctor Beta']);

        $this->actingAs($patient)
            ->get(route('patient.doctors.show', $doctorA))
            ->assertOk()
            ->assertSee('Doctor Alpha')
            ->assertSee($doctorA->speciality->name)
            ->assertSee('Prendre rendez-vous')
            ->assertSee('Aucun avis patient n’est encore enregistré')
            ->assertDontSee('Doctor Beta')
            ->assertDontSee('4.9')
            ->assertDontSee('450 MAD')
            ->assertDontSee('380 MAD')
            ->assertDontSee('CNOM');
    }

    public function test_directory_booking_cta_uses_existing_checkout_route(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $this->actingAs($patient)
            ->get(route('patient.doctors.index'))
            ->assertOk()
            ->assertSee($doctor->user->name)
            ->assertSee(route('patient.appointments.create', ['availability_id' => $slot->id]), false)
            ->assertDontSee('4.9')
            ->assertDontSee('450 MAD')
            ->assertDontSee('Instant Confirmation');
    }

    public function test_doctor_profile_booking_cta_uses_existing_checkout_route(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $this->actingAs($patient)
            ->get(route('patient.doctors.show', $doctor))
            ->assertOk()
            ->assertSee($doctor->user->name)
            ->assertSee(route('patient.appointments.create', ['availability_id' => $slot->id]), false);
    }

    public function test_checkout_uses_real_slot_and_hides_fake_stitch_prices(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $this->actingAs($patient)
            ->get(route('patient.appointments.create', ['availability_id' => $slot->id]))
            ->assertOk()
            ->assertSee('Confirmation sécurisée du rendez-vous')
            ->assertSee('Dr. '.$doctor->user->name)
            ->assertSee('name="notes"', false)
            ->assertSee('name="doctor_id"', false)
            ->assertSee('name="availability_id"', false)
            ->assertDontSee('4.9')
            ->assertDontSee('90.00')
            ->assertDontSee('450.00')
            ->assertDontSee('CNOM #');
    }

    public function test_occupied_slot_cannot_be_booked_and_is_not_offered_on_create(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $slot->update(['is_available' => false]);

        $this->actingAs($patient)
            ->get(route('patient.appointments.create', ['availability_id' => $slot->id]))
            ->assertOk()
            ->assertSee('Aucun créneau disponible');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
            ])
            ->assertSessionHasErrors('availability_id');
    }

    public function test_successful_booking_creates_pending_appointment(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $response = $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
                'notes' => 'Consultation',
            ]);

        $created = Appointment::query()
            ->where('patient_id', $patient->id)
            ->where('availability_id', $slot->id)
            ->first();

        $this->assertNotNull($created);
        $response->assertRedirect(route('patient.appointments.show', $created))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($patient)
            ->withSession(['success' => 'Demande de rendez-vous envoyée. Elle est en attente de confirmation.'])
            ->get(route('patient.appointments.show', $created))
            ->assertOk()
            ->assertSee('Étape 4 sur 4 : Terminé')
            ->assertSee('Pass confirmé')
            ->assertSee('C’est enregistré')
            ->assertDontSee('450.00')
            ->assertDontSee('CNOM #')
            ->assertDontSee('CNSS #');
    }

    public function test_forged_doctor_id_cannot_book_another_doctors_slot(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $otherDoctor = Doctor::factory()->create();

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $otherDoctor->id,
                'availability_id' => $slot->id,
            ])
            ->assertSessionHasErrors('availability_id');

        $this->assertDatabaseMissing('appointments', [
            'availability_id' => $slot->id,
        ]);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_appointment_details_page_works_for_owner(): void
    {
        $patient = User::factory()->patient()->create();
        $appointment = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(5)->setTime(10, 0),
            'notes' => 'Note visible patient',
        ]);

        $this->actingAs($patient)
            ->get(route('patient.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Dr. '.$appointment->doctor->user->name)
            ->assertSee($appointment->doctor->speciality->name)
            ->assertSee('Note visible patient')
            ->assertSee('Annuler')
            ->assertSee('Pass numérique du rendez-vous')
            ->assertDontSee('4.9')
            ->assertDontSee('90.00')
            ->assertDontSee('450.00')
            ->assertDontSee('CNOM #')
            ->assertDontSee('CNSS #');
    }

    public function test_completed_and_cancelled_appointments_do_not_expose_cancel_action(): void
    {
        $patient = User::factory()->patient()->create();
        $completed = Appointment::factory()->completed()->create([
            'patient_id' => $patient->id,
        ]);
        $cancelled = Appointment::factory()->cancelled()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(2),
        ]);
        $missed = Appointment::factory()->missed()->create([
            'patient_id' => $patient->id,
        ]);

        $this->actingAs($patient)
            ->get(route('patient.appointments.show', $completed))
            ->assertOk()
            ->assertDontSee('Annuler ce rendez-vous')
            ->assertDontSee('Modifier les notes');

        $this->actingAs($patient)
            ->get(route('patient.appointments.show', $cancelled))
            ->assertOk()
            ->assertDontSee('Annuler ce rendez-vous');

        $this->actingAs($patient)
            ->get(route('patient.appointments.show', $missed))
            ->assertOk()
            ->assertDontSee('Annuler ce rendez-vous');
    }

    public function test_patient_profile_uses_real_data_without_fake_claims(): void
    {
        $patient = User::factory()->patient()->create([
            'name' => 'ProfilePatient Unique',
            'email' => 'profilepatient.unique@example.com',
            'phone' => '0611223344',
            'address' => 'Adresse Profile Unique',
        ]);

        $this->actingAs($patient)
            ->get(route('patient.profile.edit'))
            ->assertOk()
            ->assertSee('Mon profil')
            ->assertSee('ProfilePatient Unique')
            ->assertSee('profilepatient.unique@example.com')
            ->assertSee('0611223344')
            ->assertSee('Adresse Profile Unique')
            ->assertSee('Enregistrer les modifications')
            ->assertSee('Mettre à jour le mot de passe')
            ->assertDontSee('#PT-77218')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('Hamza Chnafa')
            ->assertDontSee('chnafahamza33@gmail.com')
            ->assertDontSee('+212 661-890123')
            ->assertDontSee('Bd d\'Anfa')
            ->assertDontSee('Wallet')
            ->assertDontSee('Apple Wallet')
            ->assertDontSee('SMS')
            ->assertDontSee('05 22 89 00 00')
            ->assertDontSee('Loi 09-08')
            ->assertDontSee('Patient vérifié');

        $this->actingAs($patient)
            ->patch(route('patient.profile.update'), [
                'name' => 'ProfilePatient Updated',
                'email' => 'profilepatient.unique@example.com',
                'phone' => '0699887766',
                'address' => 'Nouvelle adresse Profile',
            ])
            ->assertRedirect(route('patient.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'ProfilePatient Updated',
            'phone' => '0699887766',
            'address' => 'Nouvelle adresse Profile',
        ]);
    }

    public function test_patient_appointments_index_uses_real_data_without_fake_claims(): void
    {
        $patient = User::factory()->patient()->create();

        $accepted = Appointment::factory()->accepted()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(3),
        ]);
        $accepted->doctor->user->update(['name' => 'UpcomingConfirm Doctor']);
        $accepted->doctor->speciality->update(['name' => 'Cardiologie Unique']);
        $accepted->doctor->update(['address' => 'Cabinet Upcoming Unique']);

        $pending = Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(5),
        ]);
        $pending->doctor->user->update(['name' => 'StillWaiting Doctor']);
        $pending->doctor->speciality->update(['name' => 'Dermatologie Unique']);

        $completed = Appointment::factory()->completed()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->subDays(8),
        ]);
        $completed->doctor->user->update(['name' => 'PastHonor Doctor']);

        $this->actingAs($patient)
            ->get(route('patient.appointments.index'))
            ->assertOk()
            ->assertSee('Mes rendez-vous')
            ->assertSee('Prendre un rendez-vous')
            ->assertSee('Dr. UpcomingConfirm Doctor')
            ->assertSee('Dr. StillWaiting Doctor')
            ->assertSee('Dr. PastHonor Doctor')
            ->assertSee('Cardiologie Unique')
            ->assertSee('Cabinet Upcoming Unique')
            ->assertSee('Rendez-vous #'.$accepted->id)
            ->assertSee('Compte #'.$patient->id)
            ->assertDontSee('#PT-77218')
            ->assertDontSee('#SC-94821')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Omar El Fassi')
            ->assertDontSee('Clinique Atlas Cardio')
            ->assertDontSee('Cabinet 302')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('CNIE')
            ->assertDontSee('DMP')
            ->assertDontSee('05 22 89 00 00')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('Hamza Chnafa')
            ->assertDontSee('Wallet')
            ->assertDontSee('SMS')
            ->assertDontSee('Loi 09-08');

        $this->actingAs($patient)
            ->get(route('patient.appointments.index', ['status' => 'upcoming']))
            ->assertOk()
            ->assertSee('Dr. UpcomingConfirm Doctor')
            ->assertDontSee('Dr. StillWaiting Doctor')
            ->assertDontSee('Dr. PastHonor Doctor');

        $this->actingAs($patient)
            ->get(route('patient.appointments.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Dr. StillWaiting Doctor')
            ->assertDontSee('Dr. UpcomingConfirm Doctor')
            ->assertDontSee('Dr. PastHonor Doctor');

        $this->actingAs($patient)
            ->get(route('patient.appointments.index', ['search' => 'UpcomingConfirm']))
            ->assertOk()
            ->assertSee('Dr. UpcomingConfirm Doctor')
            ->assertDontSee('Dr. StillWaiting Doctor');
    }

    public function test_empty_states_do_not_crash(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Aucun rendez-vous à venir')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('4.9');

        $this->actingAs($patient)
            ->get(route('patient.appointments.index'))
            ->assertOk()
            ->assertSee('Aucun rendez-vous pour le moment');

        $this->actingAs($patient)
            ->get(route('patient.doctors.index'))
            ->assertOk()
            ->assertSee('Aucun médecin disponible')
            ->assertDontSee('4.9')
            ->assertDontSee('450 MAD')
            ->assertDontSee('CNSS');

        $this->actingAs($patient)
            ->get(route('patient.doctors.index', ['search' => 'zzzz-no-match']))
            ->assertOk()
            ->assertSee('Aucun médecin ne correspond');
    }

    /**
     * @return array{patient: User, doctorUser: User, doctor: Doctor, slot: Availability}
     */
    private function bookingContext(): array
    {
        $patient = User::factory()->patient()->create();
        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $slot = Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'is_available' => true,
        ]);

        return compact('patient', 'doctorUser', 'doctor', 'slot');
    }
}
