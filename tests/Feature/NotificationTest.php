<?php

namespace Tests\Feature;

use App\Exceptions\AppointmentException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\AppointmentExpiredNotification;
use App\Notifications\AppointmentRefusedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $appointments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appointments = $this->app->make(AppointmentService::class);
    }

    public function test_new_appointment_does_not_send_a_notification(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();

        $this->appointments->create($patient, $doctor->id, $slot->id);

        Notification::assertNothingSent();
        Notification::assertNotSentTo($doctorUser, AppointmentConfirmedNotification::class);
    }

    public function test_accepting_an_appointment_notifies_the_patient(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->appointments->accept($appointment);

        Notification::assertSentTo($patient, AppointmentConfirmedNotification::class);
        Notification::assertSentToTimes($patient, AppointmentConfirmedNotification::class, 1);
        $this->assertSame(Appointment::STATUS_ACCEPTED, $appointment->fresh()->status);
    }

    public function test_rejecting_an_appointment_notifies_the_patient(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->appointments->reject($appointment);

        Notification::assertSentTo($patient, AppointmentRefusedNotification::class);
        Notification::assertSentToTimes($patient, AppointmentRefusedNotification::class, 1);
        $this->assertSame(Appointment::STATUS_REJECTED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    public function test_cancelling_an_appointment_notifies_the_patient(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->appointments->cancel($appointment);

        Notification::assertSentTo($patient, AppointmentCancelledNotification::class);
        Notification::assertNotSentTo($patient, AppointmentExpiredNotification::class);
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }

    public function test_expiring_a_pending_appointment_notifies_once(): void
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
        $this->artisan('appointments:expire')->assertSuccessful();

        Notification::assertSentToTimes($patient, AppointmentExpiredNotification::class, 1);
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }

    public function test_expiring_an_accepted_appointment_notifies_missed_once(): void
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
        $this->artisan('appointments:expire')->assertSuccessful();

        Notification::assertSentToTimes($patient, AppointmentExpiredNotification::class, 1);
        $this->assertSame(Appointment::STATUS_MISSED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);
    }

    public function test_reschedule_sends_a_single_reschedule_notification(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        Availability::factory()->create([
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
            ->assertRedirect(route('doctor.appointments.index'));

        Notification::assertSentToTimes($patient, AppointmentRescheduledNotification::class, 1);
        Notification::assertNotSentTo($patient, AppointmentCancelledNotification::class);
        Notification::assertSentToTimes($patient, AppointmentConfirmedNotification::class, 1);
    }

    public function test_same_status_update_does_not_send_duplicate_transition_notification(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);

        try {
            $this->appointments->accept($appointment->fresh());
            $this->fail('Second accept should have been rejected.');
        } catch (AppointmentException) {
            // expected
        }

        Notification::assertSentToTimes($patient, AppointmentConfirmedNotification::class, 1);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('admin.appointments.updateStatus', $appointment), [
                'status' => Appointment::STATUS_ACCEPTED,
            ])
            ->assertRedirect();

        Notification::assertSentToTimes($patient, AppointmentConfirmedNotification::class, 1);
    }

    public function test_second_reschedule_does_not_notify_again(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => $slot->date->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'is_available' => true,
        ]);

        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);
        $this->appointments->reschedule($appointment);

        try {
            $this->appointments->reschedule($appointment->fresh());
            $this->fail('Second reschedule should have been rejected.');
        } catch (AppointmentException) {
            // expected
        }

        Notification::assertSentToTimes($patient, AppointmentRescheduledNotification::class, 1);
    }

    public function test_patient_notification_goes_to_the_appointment_patient_not_the_doctor(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctorUser' => $doctorUser, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($doctorUser);
        $this->appointments->accept($appointment);

        Notification::assertSentTo($patient, AppointmentConfirmedNotification::class);
        Notification::assertNotSentTo($doctorUser, AppointmentConfirmedNotification::class);
    }

    public function test_forged_acting_user_cannot_redirect_the_notification(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $stranger = User::factory()->patient()->create();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);

        $this->actingAs($stranger);
        $this->appointments->reject($appointment);

        Notification::assertSentTo($patient, AppointmentRefusedNotification::class);
        Notification::assertNotSentTo($stranger, AppointmentRefusedNotification::class);
    }

    public function test_notification_is_dispatched_only_after_successful_transition(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);

        try {
            $this->appointments->reject($appointment->fresh());
            $this->fail('Rejecting an accepted appointment should fail.');
        } catch (AppointmentException) {
            // expected
        }

        $this->assertSame(Appointment::STATUS_ACCEPTED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);
        Notification::assertNotSentTo($patient, AppointmentRefusedNotification::class);
    }

    public function test_missing_patient_email_does_not_rollback_appointment_state(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $patient->update(['email' => '']);

        $this->appointments->accept($appointment->fresh());

        $this->assertSame(Appointment::STATUS_ACCEPTED, $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_available);
        Notification::assertNothingSent();
    }

    public function test_appointment_notifications_do_not_include_sensitive_medical_fields(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $patient->update([
            'blood_type' => 'A+',
            'weight' => 72.5,
            'height' => 175,
            'emergency_contact' => 'Personne à prévenir',
        ]);

        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id, 'Consultation de suivi');
        $this->appointments->accept($appointment);

        Notification::assertSentTo($patient, AppointmentConfirmedNotification::class, function ($notification) use ($patient) {
            $payload = json_encode($notification->toDatabase($patient));

            $this->assertStringNotContainsString('A+', $payload);
            $this->assertStringNotContainsString('72.5', $payload);
            $this->assertStringNotContainsString('175', $payload);
            $this->assertStringNotContainsString('Personne à prévenir', $payload);

            return true;
        });
    }

    public function test_complete_and_mark_missed_do_not_send_notifications(): void
    {
        Notification::fake();

        ['patient' => $patient, 'doctor' => $doctor, 'slot' => $slot] = $this->bookingContext();
        $appointment = $this->appointments->create($patient, $doctor->id, $slot->id);
        $this->appointments->accept($appointment);
        Notification::fake();

        $this->appointments->complete($appointment->fresh());

        Notification::assertNothingSent();

        ['patient' => $patientB, 'doctor' => $doctorB, 'slot' => $slotB] = $this->bookingContext();
        $missed = $this->appointments->create($patientB, $doctorB->id, $slotB->id);
        $this->appointments->accept($missed);
        Notification::fake();

        $this->appointments->markMissed($missed->fresh());

        Notification::assertNothingSent();
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
