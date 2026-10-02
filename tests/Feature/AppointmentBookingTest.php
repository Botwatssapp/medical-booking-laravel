<?php

namespace Tests\Feature;

use App\Exceptions\AppointmentException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use App\Notifications\AppointmentExpiredNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\AppointmentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $appointments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appointments = $this->app->make(AppointmentService::class);
    }

    public function test_patient_can_book_available_slot(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
                'notes' => 'Consultation',
            ])
            ->assertRedirect(route('patient.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_second_patient_cannot_book_the_same_slot(): void
    {
        ['patient' => $patientA, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $patientB = User::factory()->patient()->create();

        $this->actingAs($patientA)->post(route('patient.appointments.store'), [
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
        ])->assertRedirect(route('patient.appointments.index'));

        $this->actingAs($patientB)
            ->from(route('patient.appointments.create', ['availability_id' => $slot->id]))
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
            ])
            ->assertSessionHasErrors('availability_id');

        $this->assertSame(1, Appointment::where('availability_id', $slot->id)->count());
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_create_rejects_slot_that_still_has_an_occupying_appointment(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $otherPatient = User::factory()->patient()->create();

        Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
            'appointment_date' => $slot->date->format('Y-m-d').' '.$slot->start_time,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $slot->update(['is_available' => true]);

        $this->expectException(AppointmentException::class);

        $this->appointments->create($patient, $doctor->id, $slot->id);
    }

    public function test_doctor_can_reject_own_pending_appointment_and_slot_is_released(): void
    {
        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_REJECTED,
            ])
            ->assertRedirect(route('doctor.appointments.index'));

        $this->assertSame(Appointment::STATUS_REJECTED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_another_patient_can_rebook_slot_after_reject(): void
    {
        ['patient' => $patientA, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $patientB = User::factory()->patient()->create();

        $appointment = $this->appointments->create($patientA, $doctor->id, $slot->id);
        $this->appointments->reject($appointment);

        $this->actingAs($patientB)
            ->post(route('patient.appointments.store'), [
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
            ])
            ->assertRedirect(route('patient.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patientB->id,
            'availability_id' => $slot->id,
            'status' => Appointment::STATUS_PENDING,
        ]);
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_accept_keeps_slot_unavailable(): void
    {
        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertRedirect(route('doctor.appointments.index'));

        $this->assertSame(Appointment::STATUS_ACCEPTED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_patient_can_cancel_future_pending_appointment_and_slot_is_released(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($patient)
            ->delete(route('patient.appointments.destroy', $appointment))
            ->assertRedirect(route('patient.appointments.index'));

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_reject_does_not_release_slot_when_another_appointment_still_occupies_it(): void
    {
        ['patient' => $patientA, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $patientB = User::factory()->patient()->create();

        $occupying = $this->appointments->create($patientA, $doctor->id, $slot->id);

        try {
            Appointment::factory()->create([
                'patient_id' => $patientB->id,
                'doctor_id' => $doctor->id,
                'availability_id' => $slot->id,
                'appointment_date' => $slot->date->format('Y-m-d').' '.$slot->start_time,
                'status' => Appointment::STATUS_PENDING,
            ]);
            $this->fail('A second occupying appointment on the same slot must be rejected.');
        } catch (QueryException) {
            $this->assertSame(Appointment::STATUS_PENDING, $occupying->fresh()->status);
            $this->assertFalse($slot->fresh()->is_available);
            $this->assertSame(1, Appointment::query()->where('availability_id', $slot->id)->count());
        }
    }

    public function test_complete_keeps_slot_unavailable(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);

        $this->appointments->complete($appointment->fresh());

        $this->assertSame(Appointment::STATUS_COMPLETED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_cannot_complete_pending_appointment(): void
    {
        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->from(route('doctor.appointments.show', $appointment))
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_COMPLETED,
            ])
            ->assertRedirect(route('doctor.appointments.show', $appointment))
            ->assertSessionHas('error');

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_cannot_accept_rejected_or_cancelled_appointment(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->reject($appointment);

        $this->expectException(AppointmentException::class);
        $this->appointments->accept($appointment->fresh());
    }

    public function test_cannot_cancel_completed_or_missed_appointment(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);
        $this->appointments->complete($appointment->fresh());

        $this->expectException(AppointmentException::class);
        $this->appointments->cancel($appointment->fresh());
    }

    public function test_expire_pending_cancels_and_releases_slot(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext(
            date: now()->subDay()->toDateString(),
        );

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
            'appointment_date' => now()->subHour(),
            'status' => Appointment::STATUS_PENDING,
        ]);
        $slot->update(['is_available' => false]);

        $this->artisan('appointments:expire')->assertSuccessful();

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);

        Notification::assertSentTo($patient, AppointmentExpiredNotification::class);
    }

    public function test_expire_accepted_marks_missed_and_keeps_slot_unavailable(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext(
            date: now()->subDay()->toDateString(),
        );

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'availability_id' => $slot->id,
            'appointment_date' => now()->subHour(),
            'status' => Appointment::STATUS_ACCEPTED,
        ]);
        $slot->update(['is_available' => false]);

        $this->artisan('appointments:expire')->assertSuccessful();

        $this->assertSame(Appointment::STATUS_MISSED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);

        Notification::assertSentTo($patient, AppointmentExpiredNotification::class);
    }

    public function test_reschedule_cancels_old_appointment_and_creates_accepted_on_next_slot(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $nextSlot = Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => $slot->date->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'is_available' => true,
        ]);

        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);

        $this->actingAs($doctorUser)
            ->post(route('doctor.appointments.reschedule', $appointment))
            ->assertRedirect(route('doctor.appointments.index'))
            ->assertSessionHas('success');

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'availability_id' => $nextSlot->id,
            'status' => Appointment::STATUS_ACCEPTED,
        ]);
        $this->assertFalse($nextSlot->fresh()->is_available);

        Notification::assertSentTo($patient, AppointmentRescheduledNotification::class);
    }

    public function test_patient_cannot_modify_another_patients_appointment(): void
    {
        ['patient' => $owner, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $stranger = User::factory()->patient()->create();
        $appointment = $this->appointments->create($owner, $doctor->id, $slot->id);

        $this->actingAs($stranger)
            ->delete(route('patient.appointments.destroy', $appointment))
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_doctor_cannot_modify_another_doctors_appointment(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $otherDoctorUser = User::factory()->doctor()->create();
        Doctor::factory()->create(['user_id' => $otherDoctorUser->id]);

        $this->actingAs($otherDoctorUser)
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_patient_cannot_perform_doctor_status_transitions(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($patient)
            ->patch(route('patient.appointments.update', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($patient)
            ->patch(route('doctor.appointments.update', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_doctor_cannot_perform_admin_only_status_update(): void
    {
        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser)
            ->patch(route('admin.appointments.updateStatus', $appointment), [
                'status' => Appointment::STATUS_COMPLETED,
            ])
            ->assertForbidden();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_admin_can_cancel_and_release_slot(): void
    {
        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $admin = User::factory()->admin()->create();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($admin)
            ->post(route('admin.appointments.cancel', $appointment))
            ->assertRedirect();

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    /**
     * @return array{patient: User, doctorUser: User, doctor: Doctor, slot: Availability}
     */
    private function bookingContext(?string $date = null): array
    {
        $patient = User::factory()->patient()->create();
        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
        $slot = Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => $date ?? now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'is_available' => true,
        ]);

        return compact('patient', 'doctorUser', 'doctor', 'slot');
    }
}
