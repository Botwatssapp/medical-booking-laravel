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
            ->assertSee('Jour')
            ->assertSee('Semaine')
            ->assertSee('Mois')
            ->assertSee('Volume réel des 7 dernières semaines')
            ->assertSee('Volume réel des 7 derniers mois')
            ->assertDontSee('Les onglets Semaine et Mois restent un aperçu')
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
            ->assertSee('Privacy Patient')
            ->assertDontSee('AB+')
            ->assertDontSee('Secret Contact')
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
        $this->actingAs($admin)->get(route('admin.doctors.index'))
            ->assertOk()
            ->assertSee('Aucun médecin validé');
        $this->actingAs($admin)->get(route('admin.specialties.index'))
            ->assertOk()
            ->assertSee('Aucune spécialité');
    }

    public function test_admin_appointments_index_uses_real_data_without_fake_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create(['name' => 'ListedRdv Patient']);
        Appointment::factory()->pending()->create([
            'patient_id' => $patient->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.appointments.index'))
            ->assertOk()
            ->assertSee('Rendez-vous')
            ->assertSee('ListedRdv Patient')
            ->assertSee('En attente')
            ->assertSee('Acceptés')
            ->assertDontSee('Confirmé')
            ->assertDontSee('1,420')
            ->assertDontSee('+8.4%')
            ->assertDontSee('69%')
            ->assertDontSee('Taux 6.0%')
            ->assertDontSee('#SC-94821')
            ->assertDontSee('Mehdi Alami')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Fatima Zahra')
            ->assertDontSee('Clinique Atlas')
            ->assertDontSee('Cabinet 302')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('palpitations');
    }

    public function test_admin_doctors_create_uses_real_form_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.doctors.create'))
            ->assertOk()
            ->assertSee('Valider / Ajouter un médecin')
            ->assertSee('Aucun compte médecin en attente')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('RPPS')
            ->assertDontSee('CNOM')
            ->assertDontSee('2FA');

        $specialty = Speciality::factory()->create(['name' => 'CreateDocSpec Unique']);
        $pending = User::factory()->doctor()->create([
            'name' => 'CreateDoc Unique',
            'email' => 'createdoc.unique@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.doctors.create'))
            ->assertOk()
            ->assertSee('Valider / Ajouter un médecin')
            ->assertSee('CreateDoc Unique')
            ->assertSee('createdoc.unique@example.com')
            ->assertSee('CreateDocSpec Unique')
            ->assertSee('Valider le médecin')
            ->assertSee('Retour aux praticiens')
            ->assertDontSee('Aucun compte médecin en attente')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('RPPS')
            ->assertDontSee('CNOM')
            ->assertDontSee('2FA')
            ->assertDontSee('santeconnect.ma')
            ->assertDontSee('Sante2025')
            ->assertDontSee('10103492841')
            ->assertDontSee('MED-REG-2025')
            ->assertDontSee('400 MAD')
            ->assertDontSee('AMO')
            ->assertDontSee('téléconsultation')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin')
            ->assertDontSee('+212 522-001122')
            ->assertDontSee('Secured API v2.4')
            ->assertDontSee('Anfa')
            ->assertDontSee('Enregistrer comme brouillon');

        $this->actingAs($admin)
            ->post(route('admin.doctors.store'), [
                'user_id' => $pending->id,
                'speciality_id' => $specialty->id,
                'phone' => '0612345678',
                'address' => 'Adresse CreateDoc Unique',
                'bio' => 'Bio CreateDoc réelle',
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $this->assertDatabaseHas('doctors', [
            'user_id' => $pending->id,
            'speciality_id' => $specialty->id,
            'phone' => '0612345678',
            'address' => 'Adresse CreateDoc Unique',
            'bio' => 'Bio CreateDoc réelle',
        ]);
    }

    public function test_admin_doctors_index_uses_real_data_without_fake_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $listed = User::factory()->doctor()->create(['name' => 'IndexDoc Unique']);
        Doctor::factory()->create(['user_id' => $listed->id]);
        $other = User::factory()->doctor()->create(['name' => 'OtherDoc Unique']);
        Doctor::factory()->create(['user_id' => $other->id]);
        $pending = User::factory()->doctor()->create(['name' => 'PendingDoc Unique']);

        $this->actingAs($admin)
            ->get(route('admin.doctors.index'))
            ->assertOk()
            ->assertSee('Médecins')
            ->assertSee('IndexDoc Unique')
            ->assertSee('OtherDoc Unique')
            ->assertSee('PendingDoc Unique')
            ->assertSee('Ajouter un médecin')
            ->assertSee('Profils médecins validés')
            ->assertDontSee('+12%')
            ->assertDontSee('89%')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('Yasmine Amrani')
            ->assertDontSee('Omar El Fassi')
            ->assertDontSee('Mehdi Bennani')
            ->assertDontSee('RPPS')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('santeconnect.ma')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('3 suspendus');

        $this->actingAs($admin)
            ->get(route('admin.doctors.index', ['search' => 'IndexDoc']))
            ->assertOk()
            ->assertSee('IndexDoc Unique')
            ->assertDontSee('OtherDoc Unique')
            ->assertDontSee('PendingDoc Unique');
    }

    public function test_admin_doctors_edit_uses_real_data_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create();
        $specialty = Speciality::factory()->create(['name' => 'EditDocSpec Unique']);
        $user = User::factory()->doctor()->create([
            'name' => 'EditDoc Unique',
            'email' => 'editdoc.unique@example.com',
        ]);
        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'speciality_id' => $specialty->id,
            'phone' => '0611223344',
            'address' => 'Adresse EditDoc Unique',
            'bio' => 'Bio EditDoc réelle',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.doctors.edit', $doctor))
            ->assertOk()
            ->assertSee('Modifier le profil médecin')
            ->assertSee('EditDoc Unique')
            ->assertSee('editdoc.unique@example.com')
            ->assertSee('0611223344')
            ->assertSee('Adresse EditDoc Unique')
            ->assertSee('Bio EditDoc réelle')
            ->assertSee('EditDocSpec Unique')
            ->assertSee('Retour à la liste des médecins')
            ->assertSee('Enregistrer les modifications')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('RPPS')
            ->assertDontSee('10082739194')
            ->assertDontSee('4.9/5')
            ->assertDontSee('142 avis')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('CNSS')
            ->assertDontSee('CNOPS')
            ->assertDontSee('450 MAD')
            ->assertDontSee('santeconnect.ma')
            ->assertDontSee('Clinique Atlas')
            ->assertDontSee('Cabinet 302')
            ->assertDontSee('2FA')
            ->assertDontSee('Téléconsultation')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin')
            ->assertDontSee('Ordre National')
            ->assertDontSee('Cardiologie & Rythmologie')
            ->assertDontSee('Suspendre le praticien');

        $this->actingAs($admin)
            ->put(route('admin.doctors.update', $doctor), [
                'phone' => '0699887766',
                'address' => 'Nouvelle adresse EditDoc',
                'bio' => 'Bio mise à jour',
                'speciality_id' => $specialty->id,
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'phone' => '0699887766',
            'address' => 'Nouvelle adresse EditDoc',
            'bio' => 'Bio mise à jour',
        ]);
    }

    public function test_admin_specialties_index_uses_real_data_without_fake_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $listed = Speciality::factory()->create([
            'name' => 'AlphaSpec Unique',
            'description' => 'Description Alpha réelle',
        ]);
        Speciality::factory()->create([
            'name' => 'BetaSpec Unique',
            'description' => 'Description Beta réelle',
        ]);
        $doctorUser = User::factory()->doctor()->create(['name' => 'SpecDoc Unique']);
        Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'speciality_id' => $listed->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.specialties.index'))
            ->assertOk()
            ->assertSee('Spécialités')
            ->assertSee('Ajouter une spécialité')
            ->assertSee('AlphaSpec Unique')
            ->assertSee('BetaSpec Unique')
            ->assertSee('Description Alpha réelle')
            ->assertDontSee('+2 ce mois')
            ->assertDontSee('94.2%')
            ->assertDontSee('5.3 / spé')
            ->assertDontSee('SantéConnect v2.4')
            ->assertDontSee('Cardiologie & Rythmologie')
            ->assertDontSee('Gériatrie & Poly-pathologies')
            ->assertDontSee('Pôle Vasculaire')
            ->assertDontSee('Pôle Mère-Enfant')
            ->assertDontSee('CNDP')
            ->assertDontSee('CNOM')
            ->assertDontSee('téléconsultations')
            ->assertDontSee('thésaurus')
            ->assertDontSee('Dr. Amine B.');

        $this->actingAs($admin)
            ->get(route('admin.specialties.index', ['search' => 'AlphaSpec']))
            ->assertOk()
            ->assertSee('AlphaSpec Unique')
            ->assertDontSee('BetaSpec Unique');

        $this->actingAs($admin)
            ->get(route('admin.specialties.index', ['filter' => 'empty']))
            ->assertOk()
            ->assertSee('BetaSpec Unique')
            ->assertDontSee('AlphaSpec Unique');
    }

    public function test_admin_specialties_edit_uses_real_data_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create();
        $specialty = Speciality::factory()->create([
            'name' => 'EditSpec Unique',
            'description' => 'Description EditSpec réelle',
        ]);
        $doctorUser = User::factory()->doctor()->create([
            'name' => 'SpecEditDoc Unique',
            'email' => 'speceditdoc.unique@example.com',
        ]);
        Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'speciality_id' => $specialty->id,
            'phone' => '0611223344',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.specialties.edit', $specialty))
            ->assertOk()
            ->assertSee('Modifier la spécialité')
            ->assertSee('EditSpec Unique')
            ->assertSee('Description EditSpec réelle')
            ->assertSee('SpecEditDoc Unique')
            ->assertSee('0611223344')
            ->assertSee('Retour au catalogue des spécialités')
            ->assertSee('Enregistrer les modifications')
            ->assertDontSee('Aucun médecin rattaché')
            ->assertDontSee('Pôle Vasculaire')
            ->assertDontSee('Pôle Mère-Enfant')
            ->assertDontSee('Cardiologie & Rythmologie')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('CIM-11')
            ->assertDontSee('CARD-RYTH-01')
            ->assertDontSee('#SPEC-01-CARD')
            ->assertDontSee('FORM-ID')
            ->assertDontSee('SP-4091')
            ->assertDontSee('92.4%')
            ->assertDontSee('+5.8%')
            ->assertDontSee('4.2 jours')
            ->assertDontSee('450 MAD')
            ->assertDontSee('4.8 / 5')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Youssef Alami')
            ->assertDontSee('Laila Chraibi')
            ->assertDontSee('RPPS')
            ->assertDontSee('LOI 09-08')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin')
            ->assertDontSee('téléconsultation')
            ->assertDontSee('Actif au catalogue');

        $this->actingAs($admin)
            ->put(route('admin.specialties.update', $specialty), [
                'name' => 'EditSpec Updated',
                'description' => 'Description mise à jour',
            ])
            ->assertRedirect(route('admin.specialties.index'));

        $this->assertDatabaseHas('specialities', [
            'id' => $specialty->id,
            'name' => 'EditSpec Updated',
            'description' => 'Description mise à jour',
        ]);
    }

    public function test_admin_users_index_uses_real_data_without_fake_metrics(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'AdminListed Unique']);
        $patient = User::factory()->patient()->create([
            'name' => 'AlphaUser Unique',
            'email' => 'alphauser.unique@example.com',
            'phone' => '0611223344',
        ]);
        $other = User::factory()->patient()->create([
            'name' => 'BetaUser Unique',
            'email' => 'betauser.unique@example.com',
        ]);
        $pending = User::factory()->doctor()->create(['name' => 'PendingUser Unique']);
        $validatedUser = User::factory()->doctor()->create(['name' => 'ValidatedUser Unique']);
        Doctor::factory()->create(['user_id' => $validatedUser->id]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Utilisateurs')
            ->assertSee('Ajouter un utilisateur')
            ->assertSee('AlphaUser Unique')
            ->assertSee('BetaUser Unique')
            ->assertSee('PendingUser Unique')
            ->assertSee('ValidatedUser Unique')
            ->assertSee('alphauser.unique@example.com')
            ->assertSee('0611223344')
            ->assertSee('En attente')
            ->assertSee('Profil validé')
            ->assertDontSee('3,840')
            ->assertDontSee('+142 ce mois')
            ->assertDontSee('96.6%')
            ->assertDontSee('3,694')
            ->assertDontSee('#PT-89402')
            ->assertDontSee('#MED-4012')
            ->assertDontSee('#ADM-0018')
            ->assertDontSee('Mehdi Alami')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Kenza Filali')
            ->assertDontSee('Mehdi Bennani')
            ->assertDontSee('Nabil Zaki')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('santeconnect.ma')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin')
            ->assertDontSee('téléconsultation');

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'AlphaUser']))
            ->assertOk()
            ->assertSee('AlphaUser Unique')
            ->assertDontSee('BetaUser Unique')
            ->assertDontSee('PendingUser Unique');

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'patient']))
            ->assertOk()
            ->assertSee('AlphaUser Unique')
            ->assertSee('BetaUser Unique')
            ->assertDontSee('PendingUser Unique')
            ->assertDontSee('ValidatedUser Unique');

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('PendingUser Unique')
            ->assertDontSee('AlphaUser Unique')
            ->assertDontSee('ValidatedUser Unique');
    }

    public function test_admin_users_create_uses_real_form_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Ajouter un utilisateur')
            ->assertSee('Retour aux utilisateurs')
            ->assertSee('Créer l’utilisateur')
            ->assertSee('Nom')
            ->assertSee('Mot de passe')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('AES-256')
            ->assertDontSee('Wallet')
            ->assertDontSee('téléconsultation')
            ->assertDontSee('2FA')
            ->assertDontSee('TOTP')
            ->assertDontSee('Google Authenticator')
            ->assertDontSee('ID-SYS-USER-CREATE')
            ->assertDontSee('Youssef')
            ->assertDontSee('Benjelloun')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'CreatedUser Unique',
                'email' => 'createduser.unique@example.com',
                'phone' => '0612345678',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'patient',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'createduser.unique@example.com',
            'name' => 'CreatedUser Unique',
            'role' => 'patient',
            'phone' => '0612345678',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'AnotherUser Unique',
                'email' => 'anotheruser.unique@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'doctor',
                'add_another' => '1',
            ])
            ->assertRedirect(route('admin.users.create'));
    }

    public function test_admin_users_edit_uses_real_data_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create([
            'name' => 'EditUser Unique',
            'email' => 'edituser.unique@example.com',
            'phone' => '0699887766',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $patient))
            ->assertOk()
            ->assertSee('Modifier l’utilisateur')
            ->assertSee('EditUser Unique')
            ->assertSee('edituser.unique@example.com')
            ->assertSee('0699887766')
            ->assertSee('Retour aux utilisateurs')
            ->assertSee('Enregistrer les modifications')
            ->assertDontSee('Mehdi Alami')
            ->assertDontSee('#PT-89402')
            ->assertDontSee('SC-2026-89402')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('2FA')
            ->assertDontSee('CNIE')
            ->assertDontSee('BE894021')
            ->assertDontSee('Dr. Amine B.')
            ->assertDontSee('Super Admin')
            ->assertDontSee('Aujourd\'hui, 10:42');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $patient), [
                'name' => 'EditUser Updated',
                'email' => 'edituser.unique@example.com',
                'phone' => '0611111111',
                'role' => 'patient',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'EditUser Updated',
            'phone' => '0611111111',
        ]);
    }

    public function test_admin_profile_uses_real_account_without_fake_claims(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'ProfileAdmin Unique',
            'email' => 'profileadmin.unique@example.com',
            'phone' => '0612345678',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Mon profil')
            ->assertSee('ProfileAdmin Unique')
            ->assertSee('profileadmin.unique@example.com')
            ->assertSee('0612345678')
            ->assertSee('Administrateur')
            ->assertSee('Compte #'.$admin->id)
            ->assertSee('8 caractères minimum')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('2FA')
            ->assertDontSee('CNIE')
            ->assertDontSee('ISO 27001')
            ->assertDontSee('Dr. Amine')
            ->assertDontSee('Super Admin')
            ->assertDontSee('santeconnect.ma')
            ->assertDontSee('ADM-0018')
            ->assertDontSee('196.200.145.22')
            ->assertDontSee('Casablanca')
            ->assertDontSee('K9#mQ$8x!2vP')
            ->assertDontSee('12 caractères')
            ->assertDontSee('100% sécurisé');

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'first_name' => 'ProfileAdmin',
                'last_name' => 'Updated',
                'email' => 'profileadmin.unique@example.com',
                'phone' => '0699999999',
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'ProfileAdmin Updated',
            'phone' => '0699999999',
        ]);
    }
}
