<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_obsolete_breeze_and_api_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('dashboard'));
        $this->assertFalse(Route::has('profile.edit'));
        $this->assertFalse(Route::has('api.availabilities'));

        $this->get('/dashboard')->assertNotFound();
        $this->get('/profile')->assertNotFound();
        $this->get('/api/doctor/1/availabilities')->assertNotFound();
        $this->get('/this-route-does-not-exist-phase13')->assertNotFound();
    }

    public function test_dead_root_controllers_are_removed(): void
    {
        $this->assertFileDoesNotExist(app_path('Http/Controllers/AppointmentController.php'));
        $this->assertFileDoesNotExist(app_path('Http/Controllers/ProfileController.php'));
        $this->assertFileDoesNotExist(app_path('View/Components/AppLayout.php'));
        $this->assertFileDoesNotExist(base_path('routes/api.php'));
    }

    public function test_role_dashboards_use_named_routes_not_generic_dashboard(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $admin = User::factory()->admin()->create();

        $this->assertSame(route('patient.dashboard', absolute: false), $patient->dashboardPath());
        $this->assertSame(route('doctor.dashboard', absolute: false), $doctor->dashboardPath());
        $this->assertSame(route('admin.dashboard', absolute: false), $admin->dashboardPath());
    }

    public function test_patient_cannot_mark_another_patients_notification_read(): void
    {
        $owner = User::factory()->patient()->create();
        $intruder = User::factory()->patient()->create();
        $appointment = Appointment::factory()->accepted()->create([
            'patient_id' => $owner->id,
        ]);

        $owner->notify(new AppointmentConfirmedNotification($appointment));
        $notification = $owner->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $this->actingAs($intruder)
            ->patch(route('patient.notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_guest_is_forbidden_from_role_areas_via_redirect(): void
    {
        $this->get(route('patient.dashboard'))->assertRedirect(route('login'));
        $this->get(route('doctor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
