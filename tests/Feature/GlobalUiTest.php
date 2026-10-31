<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GlobalUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_accessible_labels_and_no_fake_claims(): void
    {
        $html = $this->get(route('login'))
            ->assertOk()
            ->assertSee('for="email"', false)
            ->assertSee('for="pw"', false)
            ->assertSee('Se connecter')
            ->getContent();

        $this->assertStringNotContainsString('Rappels automatiques', $html);
        $this->assertStringNotContainsString('Données 100% sécurisées', $html);
        $this->assertStringNotContainsString('500+', $html);
        $this->assertStringNotContainsString('10k+', $html);
        $this->assertStringNotContainsString('50k+', $html);
        $this->assertStringNotContainsString('2,500+', $html);
        $this->assertStringNotContainsString('Yassine', $html);
        $this->assertStringNotContainsString('Benali', $html);
        $this->assertStringNotContainsString('CNSS', $html);
        $this->assertStringNotContainsString('HIPAA', $html);
        $this->assertStringNotContainsString('CNDP', $html);
        $this->assertStringNotContainsString('HDS', $html);
        $this->assertStringNotContainsString('AES', $html);
        $this->assertStringNotContainsString('karim.tazi', $html);
        $this->assertStringNotContainsString('P-88219', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_register_page_has_no_fake_marketing_stats(): void
    {
        $html = $this->get(route('register'))
            ->assertOk()
            ->assertSee('for="name"', false)
            ->assertSee('for="email"', false)
            ->assertSee('Créer mon compte')
            ->getContent();

        $this->assertStringNotContainsString('500+', $html);
        $this->assertStringNotContainsString('10k+', $html);
        $this->assertStringNotContainsString('50k+', $html);
        $this->assertStringNotContainsString('des milliers', $html);
        $this->assertStringNotContainsString('4.9', $html);
        $this->assertStringNotContainsString('150 000', $html);
        $this->assertStringNotContainsString('4 500', $html);
        $this->assertStringNotContainsString('CNSS', $html);
        $this->assertStringNotContainsString('CNOPS', $html);
        $this->assertStringNotContainsString('CNDP', $html);
        $this->assertStringNotContainsString('HIPAA', $html);
        $this->assertStringNotContainsString('HDS', $html);
        $this->assertStringNotContainsString('AES', $html);
        $this->assertStringNotContainsString('Yasmine', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_guest_home_renders_login_not_welcome_or_dashboard(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Se connecter')
            ->assertDontSee('Laravel');

        $this->assertFalse(Route::has('dashboard'));
        $this->assertFalse(Route::has('profile.edit'));
    }

    public function test_password_pages_are_french_and_branded(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Mot de passe oublié')
            ->assertSee('SantéConnect')
            ->assertSee('Adresse e-mail')
            ->assertSee('for="email"', false)
            ->assertDontSee('Laravel')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('AES 256')
            ->assertDontSee('HIPAA')
            ->assertDontSee('Cloudflare')
            ->assertDontSee('15 minutes')
            ->assertDontSee('05 22 89 00 00');

        $this->get(route('password.reset', ['token' => 'demo-token']))
            ->assertOk()
            ->assertSee('Nouveau mot de passe')
            ->assertSee('SantéConnect')
            ->assertSee('Adresse e-mail')
            ->assertSee('for="email"', false)
            ->assertSee('Enregistrer le mot de passe')
            ->assertDontSee('Laravel')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('AES')
            ->assertDontSee('HIPAA')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('TKN-8831')
            ->assertDontSee('Atlas#2024Health!')
            ->assertDontSee('Très Robuste')
            ->assertDontSee('Zero-Knowledge')
            ->assertDontSee('05 22 89 00 00')
            ->assertDontSee('sante.ma');

        $this->withSession([
            'password_reset_confirmed' => true,
            'password_reset_name' => 'Darion Murphy',
            'password_reset_email' => 'darion@example.com',
            'password_reset_role' => 'patient',
        ])->get(route('password.updated'))
            ->assertOk()
            ->assertSee('Mot de passe modifié')
            ->assertSee('Darion Murphy')
            ->assertSee('d***@example.com')
            ->assertSee('Se connecter')
            ->assertDontSee('Laravel')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('AES')
            ->assertDontSee('HIPAA')
            ->assertDontSee('Karim Tazi')
            ->assertDontSee('05 22 89 00 00')
            ->assertDontSee('MA-739201')
            ->assertDontSee('8F2a-99X')
            ->assertDontSee('Argon2id')
            ->assertDontSee('sante.ma');
    }

    public function test_patient_layout_has_skip_link_and_active_navigation(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Aller au contenu')
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('patient.doctors.index'), false)
            ->assertSee(route('patient.appointments.index'), false)
            ->assertSee('Déconnexion');
    }

    public function test_success_flash_appears_once_on_patient_pages(): void
    {
        $patient = User::factory()->patient()->create();

        $html = $this->actingAs($patient)
            ->withSession(['success' => 'Message unique Phase 12'])
            ->get(route('patient.profile.edit'))
            ->assertOk()
            ->assertSee('Message unique Phase 12')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Message unique Phase 12'));
        $this->assertStringContainsString('role="status"', $html);
    }

    public function test_doctor_and_admin_layouts_keep_role_navigation(): void
    {
        $pendingDoctor = User::factory()->doctor()->create();
        $this->actingAs($pendingDoctor)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Profil en attente de validation')
            ->assertSee('Mon profil')
            ->assertDontSee('route(\'dashboard\')');

        $operational = Doctor::factory()->create();
        $this->actingAs($operational->user)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Rendez-vous')
            ->assertSee('Disponibilités')
            ->assertSee('aria-current="page"', false);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Utilisateurs')
            ->assertSee('Spécialités')
            ->assertDontSee('10,000')
            ->assertDontSee('99%');
    }

    public function test_status_badges_use_existing_labels(): void
    {
        $patient = User::factory()->patient()->create();
        $appointment = Appointment::factory()->accepted()->create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDays(2),
        ]);

        $this->actingAs($patient)
            ->get(route('patient.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Confirmé')
            ->assertSee('Statut : Confirmé');

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Accepté')
            ->assertSee('Statut : Accepté');
    }

    public function test_landing_uses_real_counts_and_hides_fake_marketing(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('SantéConnect')
            ->assertSee('Explorer les spécialités')
            ->assertSee('Aucun avis patient n’est encore enregistré')
            ->assertDontSee('Laravel')
            ->assertDontSee('2,500+')
            ->assertDontSee('98%')
            ->assertDontSee('150,000+')
            ->assertDontSee('4.9')
            ->assertDontSee('Sarah Benali')
            ->assertDontSee('Amina Mansouri')
            ->assertDontSee('HIPAA')
            ->assertDontSee('€60')
            ->assertDontSee('View all 42');
    }

    public function test_two_factor_demo_page_is_visual_only(): void
    {
        $this->get(route('demo.two-factor'))
            ->assertOk()
            ->assertSee('Vérification en deux étapes')
            ->assertSee('SantéConnect')
            ->assertSee('Valider et accéder à mon Espace')
            ->assertSee('Aucun SMS envoyé')
            ->assertDontSee('Laravel')
            ->assertDontSee('CNDP')
            ->assertDontSee('HDS')
            ->assertDontSee('AES')
            ->assertDontSee('HIPAA')
            ->assertDontSee('TLS')
            ->assertDontSee('RGPD')
            ->assertDontSee('Loi 09-08')
            ->assertDontSee('05 22 89 00 00')
            ->assertDontSee('Casablanca')
            ->assertDontSee('k•••••@email.com')
            ->assertDontSee('••••••••89')
            ->assertDontSee('04:48')
            ->assertDontSee('value="8"', false)
            ->assertDontSee('value="4"', false);
    }
}
