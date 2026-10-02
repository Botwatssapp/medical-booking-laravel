<?php

namespace Tests\Feature;

use App\Exceptions\AvailabilityException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_doctors_user_id_is_rejected_by_database(): void
    {
        $user = User::factory()->doctor()->create();
        Doctor::factory()->create(['user_id' => $user->id]);

        try {
            Doctor::factory()->create(['user_id' => $user->id]);
            $this->fail('A second doctors row for the same user_id should be rejected.');
        } catch (QueryException) {
            $this->assertSame(1, Doctor::query()->where('user_id', $user->id)->count());
        }
    }

    public function test_two_occupying_appointments_cannot_share_one_availability(): void
    {
        $slot = Availability::factory()->create();
        Appointment::factory()->pending()->create(['availability_id' => $slot->id]);

        try {
            Appointment::factory()->accepted()->create(['availability_id' => $slot->id]);
            $this->fail('A second occupying appointment on the same slot should be rejected.');
        } catch (QueryException) {
            $this->assertSame(1, Appointment::query()->where('availability_id', $slot->id)->count());
        }
    }

    public function test_cancelled_history_allows_a_new_occupying_appointment(): void
    {
        $slot = Availability::factory()->create();
        $previous = Appointment::factory()->pending()->create(['availability_id' => $slot->id]);
        $previous->update(['status' => Appointment::STATUS_CANCELLED]);

        $next = Appointment::factory()->pending()->create(['availability_id' => $slot->id]);

        $this->assertSame($slot->id, $next->availability_id);
        $this->assertSame(2, Appointment::query()->where('availability_id', $slot->id)->count());
    }

    public function test_rejected_history_allows_a_new_occupying_appointment(): void
    {
        $slot = Availability::factory()->create();
        $previous = Appointment::factory()->pending()->create(['availability_id' => $slot->id]);
        $previous->update(['status' => Appointment::STATUS_REJECTED]);

        $next = Appointment::factory()->accepted()->create(['availability_id' => $slot->id]);

        $this->assertSame($slot->id, $next->availability_id);
        $this->assertSame(2, Appointment::query()->where('availability_id', $slot->id)->count());
    }

    public function test_completed_appointment_remains_occupying_at_database_level(): void
    {
        $slot = Availability::factory()->create();
        Appointment::factory()->completed()->create(['availability_id' => $slot->id]);

        try {
            Appointment::factory()->pending()->create(['availability_id' => $slot->id]);
            $this->fail('A completed appointment must keep the slot occupied.');
        } catch (QueryException) {
            $this->assertSame(1, Appointment::query()->where('availability_id', $slot->id)->count());
        }
    }

    public function test_missed_appointment_remains_occupying_at_database_level(): void
    {
        $slot = Availability::factory()->create();
        Appointment::factory()->missed()->create(['availability_id' => $slot->id]);

        try {
            Appointment::factory()->pending()->create(['availability_id' => $slot->id]);
            $this->fail('A missed appointment must keep the slot occupied.');
        } catch (QueryException) {
            $this->assertSame(1, Appointment::query()->where('availability_id', $slot->id)->count());
        }
    }

    public function test_soft_deleting_used_speciality_does_not_delete_doctors(): void
    {
        $speciality = Speciality::factory()->create();
        $doctor = Doctor::factory()->create(['speciality_id' => $speciality->id]);

        $speciality->delete();

        $this->assertSoftDeleted($speciality);
        $this->assertTrue(Doctor::query()->whereKey($doctor->id)->exists());
        $this->assertSame($speciality->id, $doctor->fresh()->speciality_id);
    }

    public function test_force_deleting_used_speciality_is_restricted(): void
    {
        $speciality = Speciality::factory()->create();
        $doctor = Doctor::factory()->create(['speciality_id' => $speciality->id]);

        try {
            $speciality->forceDelete();
            $this->fail('Force-deleting a speciality still used by a doctor should be restricted.');
        } catch (QueryException) {
            $this->assertTrue(Doctor::query()->whereKey($doctor->id)->exists());
            $this->assertTrue(Speciality::withTrashed()->whereKey($speciality->id)->exists());
        }
    }

    public function test_unused_speciality_can_be_force_deleted(): void
    {
        $speciality = Speciality::factory()->create();

        $speciality->forceDelete();

        $this->assertDatabaseMissing('specialities', ['id' => $speciality->id]);
    }

    public function test_exact_availability_duplicate_is_rejected_by_database(): void
    {
        $slot = Availability::factory()->create();

        try {
            Availability::query()->create([
                'doctor_id' => $slot->doctor_id,
                'date' => $slot->date->toDateString(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'is_available' => true,
            ]);
            $this->fail('An exact availability duplicate should be rejected.');
        } catch (QueryException) {
            $this->assertSame(1, Availability::query()->where('doctor_id', $slot->doctor_id)->count());
        }
    }

    public function test_adjacent_availability_slots_remain_possible(): void
    {
        $doctor = Doctor::factory()->create();
        $date = Carbon::now()->addDays(3)->toDateString();
        $service = app(AvailabilityService::class);

        $service->create($doctor, $date, '10:00', '11:00');
        $service->create($doctor, $date, '11:00', '12:00');

        $this->assertSame(2, Availability::query()->where('doctor_id', $doctor->id)->whereDate('date', $date)->count());
    }

    public function test_overlapping_availability_remains_rejected_by_service(): void
    {
        $doctor = Doctor::factory()->create();
        $date = Carbon::now()->addDays(4)->toDateString();
        $service = app(AvailabilityService::class);

        $service->create($doctor, $date, '09:00', '10:00');

        $this->expectException(AvailabilityException::class);
        $service->create($doctor, $date, '09:30', '10:30');
    }
}
