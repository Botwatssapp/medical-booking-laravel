<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WebsiteAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_error_pages_are_branded(): void
    {
        $this->get('/this-route-does-not-exist-audit')
            ->assertNotFound()
            ->assertSee('SantéConnect')
            ->assertSee('Page introuvable')
            ->assertDontSee('Laravel');

        $patient = User::factory()->patient()->create();
        $this->actingAs($patient)
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('SantéConnect')
            ->assertSee('Accès refusé')
            ->assertDontSee('Laravel');

        $this->assertFileExists(resource_path('views/errors/419.blade.php'));
        $this->assertFileExists(resource_path('views/errors/500.blade.php'));
        $this->assertFileExists(resource_path('views/errors/422.blade.php'));
        $this->assertFileExists(public_path('favicon.svg'));
    }

    public function test_role_areas_stay_isolated(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = Doctor::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($patient)->get(route('doctor.dashboard'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($doctor->user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($doctor->user)->get(route('patient.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('patient.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('doctor.appointments.index'))->assertForbidden();
    }

    public function test_core_named_routes_and_views_are_wired(): void
    {
        $expected = [
            'home', 'landing', 'login', 'register', 'password.request',
            'patient.dashboard', 'patient.doctors.index', 'patient.appointments.index',
            'patient.profile.edit', 'patient.notifications.index',
            'doctor.dashboard', 'doctor.appointments.index', 'doctor.availabilities.index',
            'doctor.availabilities.create', 'doctor.profile.edit',
            'admin.dashboard', 'admin.doctors.index', 'admin.doctors.create',
            'admin.specialties.index', 'admin.specialties.create',
            'admin.users.index', 'admin.users.create',
            'admin.appointments.index', 'admin.profile.edit',
        ];

        foreach ($expected as $name) {
            $this->assertTrue(Route::has($name), "Route manquante : {$name}");
        }
    }

    public function test_patient_and_doctor_search_forms_have_submit_buttons(): void
    {
        $patient = User::factory()->patient()->create();
        $this->actingAs($patient)
            ->get(route('patient.appointments.index'))
            ->assertOk()
            ->assertSee('Rechercher')
            ->assertSee(route('patient.profile.edit'), false);

        $doctor = Doctor::factory()->create();
        $this->actingAs($doctor->user)
            ->get(route('doctor.appointments.index'))
            ->assertOk()
            ->assertSee('Rechercher');
    }

    public function test_public_pages_expose_favicon_and_landing_description(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('favicon.svg', false)
            ->assertSee('name="description"', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('favicon.svg', false)
            ->assertSee('Inscription');
    }
}
