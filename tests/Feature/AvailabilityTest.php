<?php

namespace Tests\Feature;

use App\Exceptions\AvailabilityException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\AvailabilityService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilities;

    private AppointmentService $appointments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilities = $this->app->make(AvailabilityService::class);
        $this->appointments = $this->app->make(AppointmentService::class);
    }

    public function test_doctor_can_create_own_availability(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($doctorUser)
            ->post(route('doctor.availabilities.store'), [
                'date' => $date,
                'start_time' => '10:00',
                'end_time' => '10:30',
                'doctor_id' => 999,
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $created = Availability::where('doctor_id', $doctor->id)->first();
        $this->assertNotNull($created);
        $this->assertSame($date, $created->date->toDateString());
        $this->assertSame('10:00:00', $created->start_time);
        $this->assertSame('10:30:00', $created->end_time);
        $this->assertTrue($created->is_available);
    }

    public function test_doctor_cannot_modify_another_doctors_availability(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();
        $other = $this->doctorContext();
        $slot = $this->slotFor($other['doctor']);

        $this->actingAs($doctorUser)
            ->put('/doctor/availabilities/'.$slot->id, [
                'date' => now()->addDays(4)->toDateString(),
                'start_time' => '11:00',
                'end_time' => '11:30',
            ])
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('availabilities', [
            'id' => $slot->id,
            'doctor_id' => $other['doctor']->id,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);
    }

    public function test_doctor_cannot_delete_another_doctors_availability(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();
        $other = $this->doctorContext();
        $slot = $this->slotFor($other['doctor']);

        $this->actingAs($doctorUser)
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertForbidden();

        $this->assertDatabaseHas('availabilities', ['id' => $slot->id]);
    }

    public function test_patient_cannot_manage_doctor_availability(): void
    {
        $patient = User::factory()->patient()->create();
        ['doctor' => $doctor] = $this->doctorContext();
        $slot = $this->slotFor($doctor);

        $this->actingAs($patient)
            ->get(route('doctor.availabilities.index'))
            ->assertForbidden();

        $this->actingAs($patient)
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertForbidden();

        $this->actingAs($patient)
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertForbidden();
    }

    public function test_admin_cannot_manage_doctor_availability_routes(): void
    {
        $admin = User::factory()->admin()->create();
        ['doctor' => $doctor] = $this->doctorContext();
        $slot = $this->slotFor($doctor);

        $this->actingAs($admin)
            ->get(route('doctor.availabilities.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertForbidden();
    }

    public function test_invalid_time_range_is_rejected(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => 'not-a-time',
                'end_time' => '10:30',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('start_time');

        $this->assertSame(0, Availability::count());
    }

    public function test_end_time_before_start_is_rejected(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '11:00',
                'end_time' => '10:00',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('end_time');

        $this->assertSame(0, Availability::count());
    }

    public function test_past_and_today_dates_are_rejected(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('date');

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => now()->subDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('date');

        $this->assertSame(0, Availability::count());
    }

    public function test_duplicate_availability_is_rejected(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();
        $this->slotFor($doctor, $date, '10:00:00', '10:30:00');

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => $date,
                'start_time' => '10:00',
                'end_time' => '10:30',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, Availability::where('doctor_id', $doctor->id)->count());
    }

    public function test_overlapping_availability_is_rejected(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();
        $this->slotFor($doctor, $date, '10:00:00', '11:00:00');

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.create'))
            ->post(route('doctor.availabilities.store'), [
                'date' => $date,
                'start_time' => '10:30',
                'end_time' => '11:30',
            ])
            ->assertRedirect(route('doctor.availabilities.create'))
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, Availability::where('doctor_id', $doctor->id)->count());
    }

    public function test_adjacent_availability_is_allowed(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();
        $this->slotFor($doctor, $date, '10:00:00', '11:00:00');

        $this->actingAs($doctorUser)
            ->post(route('doctor.availabilities.store'), [
                'date' => $date,
                'start_time' => '11:00',
                'end_time' => '12:00',
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertSame(2, Availability::where('doctor_id', $doctor->id)->count());
    }

    public function test_unbooked_availability_can_be_deleted(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $slot = $this->slotFor($doctor);

        $this->actingAs($doctorUser)
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertRedirect(route('doctor.availabilities.index'));

        $this->assertDatabaseMissing('availabilities', ['id' => $slot->id]);
    }

    public function test_booked_availability_cannot_be_deleted(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $patient = User::factory()->patient()->create();
        $slot = $this->slotFor($doctor);
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->from(route('doctor.availabilities.index'))
            ->delete(route('doctor.availabilities.destroy', $slot))
            ->assertRedirect(route('doctor.availabilities.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('availabilities', ['id' => $slot->id]);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'availability_id' => $slot->id,
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    public function test_rejecting_an_appointment_releases_the_slot_through_appointment_service(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $patient = User::factory()->patient()->create();
        $slot = $this->slotFor($doctor);
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->appointments->reject($appointment);

        $this->assertSame(Appointment::STATUS_REJECTED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_cancelling_an_appointment_releases_the_slot_through_appointment_service(): void
    {
        ['doctor' => $doctor] = $this->doctorContext();
        $patient = User::factory()->patient()->create();
        $slot = $this->slotFor($doctor);
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->appointments->cancel($appointment);

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_another_occupying_appointment_prevents_slot_release(): void
    {
        ['doctor' => $doctor] = $this->doctorContext();
        $patientA = User::factory()->patient()->create();
        $patientB = User::factory()->patient()->create();
        $slot = $this->slotFor($doctor);

        $first = $this->appointments->create($patientA, $doctor->id, $slot->id);

        try {
            Appointment::factory()->create([
                'patient_id' => $patientB->id,
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
                'appointment_date' => $slot->date->format('Y-m-d').' '.$slot->start_time,
                'status' => Appointment::STATUS_ACCEPTED,
            ]);
            $this->fail('A second occupying appointment on the same slot must be rejected.');
        } catch (QueryException) {
            $this->assertSame(Appointment::STATUS_PENDING, $first->fresh()->status);
            $this->assertFalse($slot->fresh()->is_available);
            $this->assertSame(1, Appointment::query()->where('availability_id', $slot->id)->count());
        }
    }

    public function test_availability_update_is_not_supported(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $slot = $this->slotFor($doctor);

        $this->actingAs($doctorUser)
            ->put('/doctor/availabilities/'.$slot->id, [
                'date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '14:30',
            ])
            ->assertMethodNotAllowed();

        $this->actingAs($doctorUser)
            ->get('/doctor/availabilities/'.$slot->id.'/edit')
            ->assertNotFound();
    }

    public function test_updating_another_doctors_availability_is_forbidden(): void
    {
        ['doctorUser' => $doctorUser] = $this->doctorContext();
        $other = $this->doctorContext();
        $slot = $this->slotFor($other['doctor']);

        $this->actingAs($doctorUser)
            ->patch('/doctor/availabilities/'.$slot->id, [
                'date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '14:30',
            ])
            ->assertMethodNotAllowed();
    }

    public function test_updating_a_booked_slot_is_not_supported(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $patient = User::factory()->patient()->create();
        $slot = $this->slotFor($doctor);
        $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->put('/doctor/availabilities/'.$slot->id, [
                'date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '14:30',
            ])
            ->assertMethodNotAllowed();

        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_crafted_doctor_id_cannot_hijack_ownership(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $other = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($doctorUser)
            ->post(route('doctor.availabilities.store'), [
                'doctor_id' => $other['doctor']->id,
                'date' => $date,
                'start_time' => '09:00',
                'end_time' => '09:30',
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $created = Availability::where('doctor_id', $doctor->id)
            ->where('start_time', '09:00:00')
            ->first();
        $this->assertNotNull($created);
        $this->assertSame($date, $created->date->toDateString());

        $this->assertFalse(
            Availability::where('doctor_id', $other['doctor']->id)
                ->whereDate('date', $date)
                ->exists()
        );
    }

    public function test_guests_are_redirected_to_login(): void
    {
        ['doctor' => $doctor] = $this->doctorContext();
        $slot = $this->slotFor($doctor);

        $this->get(route('doctor.availabilities.index'))
            ->assertRedirect(route('login'));

        $this->post(route('doctor.availabilities.store'), [
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:30',
        ])->assertRedirect(route('login'));

        $this->delete(route('doctor.availabilities.destroy', $slot))
            ->assertRedirect(route('login'));
    }

    public function test_generate_creates_non_overlapping_slots_and_skips_duplicates(): void
    {
        ['doctorUser' => $doctorUser, 'doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(3)->toDateString();
        $this->slotFor($doctor, $date, '09:00:00', '09:30:00');

        $this->actingAs($doctorUser)
            ->post(route('doctor.availabilities.store'), [
                'mode' => 'generate',
                'date' => $date,
                'period_start' => '09:00',
                'period_end' => '10:30',
                'duration_minutes' => 30,
            ])
            ->assertRedirect(route('doctor.availabilities.index'));

        $slots = Availability::where('doctor_id', $doctor->id)->whereDate('date', $date)
            ->orderBy('start_time')
            ->get();

        $this->assertCount(3, $slots);
        $this->assertSame('09:00:00', $slots[0]->start_time);
        $this->assertSame('09:30:00', $slots[1]->start_time);
        $this->assertSame('10:00:00', $slots[2]->start_time);
    }

    public function test_service_rejects_duplicate_and_overlap_directly(): void
    {
        ['doctor' => $doctor] = $this->doctorContext();
        $date = now()->addDays(4)->toDateString();

        $this->availabilities->create($doctor, $date, '10:00', '11:00');

        try {
            $this->availabilities->create($doctor, $date, '10:00', '11:00');
            $this->fail('Duplicate availability should have been rejected.');
        } catch (AvailabilityException $e) {
            $this->assertStringContainsString('existe déjà', $e->getMessage());
        }

        try {
            $this->availabilities->create($doctor, $date, '10:30', '11:30');
            $this->fail('Overlapping availability should have been rejected.');
        } catch (AvailabilityException $e) {
            $this->assertStringContainsString('chevauche', $e->getMessage());
        }
    }

    /**
     * @return array{doctorUser: User, doctor: Doctor}
     */
    private function doctorContext(): array
    {
        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        return compact('doctorUser', 'doctor');
    }

    private function slotFor(
        Doctor $doctor,
        ?string $date = null,
        string $start = '10:00:00',
        string $end = '10:30:00',
    ): Availability {
        return Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => $date ?? now()->addDays(2)->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'is_available' => true,
        ]);
    }
}
