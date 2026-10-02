<?php

namespace App\Services;

use App\Exceptions\AppointmentException;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur métier des rendez-vous médicaux.
 *
 * Centralise création, transitions de statut, expiration, report
 * et libération des créneaux. L'autorisation reste dans les Policies
 * et les contrôleurs.
 */
class AppointmentService
{
    public function __construct(private readonly AppointmentNotifier $notifier) {}

    /**
     * Réserve un créneau pour un patient (statut pending).
     *
     * `lockForUpdate()` empêche deux réservations simultanées
     * du même créneau dans une transaction.
     */
    public function create(User $patient, int $doctorId, int $availabilityId, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($patient, $doctorId, $availabilityId, $notes) {
            $availability = Availability::query()
                ->lockForUpdate()
                ->whereKey($availabilityId)
                ->where('doctor_id', $doctorId)
                ->where('is_available', true)
                ->first();

            if (! $availability) {
                throw new AppointmentException('Ce créneau n\'est plus disponible.');
            }

            if ($this->hasOccupyingAppointment($availability->id)) {
                throw new AppointmentException('Ce créneau n\'est plus disponible.');
            }

            $appointment = $patient->appointments()->create([
                'doctor_id' => $doctorId,
                'availability_id' => $availability->id,
                'appointment_date' => $availability->date->format('Y-m-d').' '.$availability->start_time,
                'status' => Appointment::STATUS_PENDING,
                'notes' => $notes,
            ]);

            $availability->update(['is_available' => false]);

            return $appointment;
        });
    }

    /**
     * pending → accepted. Le créneau reste occupé.
     */
    public function accept(Appointment $appointment): Appointment
    {
        $updated = DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);
            $this->assertTransition($appointment, Appointment::STATUS_PENDING, 'accepté');

            $appointment->update(['status' => Appointment::STATUS_ACCEPTED]);

            return $appointment->refresh();
        });

        $this->notifier->notifyAccepted($updated);

        return $updated;
    }

    /**
     * pending → rejected. Libère le créneau s'il n'est plus occupé.
     */
    public function reject(Appointment $appointment): Appointment
    {
        $updated = DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);
            $this->assertTransition($appointment, Appointment::STATUS_PENDING, 'refusé');

            $appointment->update(['status' => Appointment::STATUS_REJECTED]);
            $this->releaseAvailabilityIfFree($appointment->availability_id);

            return $appointment->refresh();
        });

        $this->notifier->notifyRejected($updated);

        return $updated;
    }

    /**
     * Annule un rendez-vous (→ cancelled) et libère le créneau si possible.
     *
     * @param  bool  $requireFuture  Si true (patient), refuse les rendez-vous passés.
     */
    public function cancel(Appointment $appointment, bool $requireFuture = false): Appointment
    {
        $updated = DB::transaction(function () use ($appointment, $requireFuture) {
            $appointment = $this->lockAppointment($appointment);

            if (in_array($appointment->status, [
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_MISSED,
            ], true)) {
                throw new AppointmentException('Ce rendez-vous ne peut pas être annulé.');
            }

            if ($appointment->status === Appointment::STATUS_CANCELLED) {
                throw new AppointmentException('Ce rendez-vous est déjà annulé.');
            }

            if ($requireFuture && $appointment->appointment_date->isPast()) {
                throw new AppointmentException('Impossible d\'annuler un rendez-vous passé.');
            }

            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
            $this->releaseAvailabilityIfFree($appointment->availability_id);

            return $appointment->refresh();
        });

        $this->notifier->notifyCancelled($updated);

        return $updated;
    }

    /**
     * accepted → completed. Le créneau reste occupé (historique).
     */
    public function complete(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);
            $this->assertTransition($appointment, Appointment::STATUS_ACCEPTED, 'marqué comme terminé');

            $appointment->update(['status' => Appointment::STATUS_COMPLETED]);

            return $appointment->refresh();
        });
    }

    /**
     * accepted → missed. Le créneau reste occupé.
     */
    public function markMissed(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);
            $this->assertTransition($appointment, Appointment::STATUS_ACCEPTED, 'marqué comme manqué');

            $appointment->update(['status' => Appointment::STATUS_MISSED]);

            return $appointment->refresh();
        });
    }

    /**
     * pending + date passée → cancelled. Libère le créneau si possible.
     */
    public function expirePending(Appointment $appointment): Appointment
    {
        $updated = DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);

            if ($appointment->status !== Appointment::STATUS_PENDING) {
                throw new AppointmentException('Seuls les rendez-vous en attente peuvent expirer vers annulé.');
            }

            if (! $appointment->appointment_date->isPast()) {
                throw new AppointmentException('Ce rendez-vous n\'est pas encore expiré.');
            }

            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
            $this->releaseAvailabilityIfFree($appointment->availability_id);

            return $appointment->refresh();
        });

        $this->notifier->notifyExpired($updated, 'cancelled');

        return $updated;
    }

    /**
     * accepted + date passée → missed. Le créneau reste occupé.
     */
    public function expireAccepted(Appointment $appointment): Appointment
    {
        $updated = DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);

            if ($appointment->status !== Appointment::STATUS_ACCEPTED) {
                throw new AppointmentException('Seuls les rendez-vous acceptés peuvent expirer vers manqué.');
            }

            if (! $appointment->appointment_date->isPast()) {
                throw new AppointmentException('Ce rendez-vous n\'est pas encore expiré.');
            }

            $appointment->update(['status' => Appointment::STATUS_MISSED]);

            return $appointment->refresh();
        });

        $this->notifier->notifyExpired($updated, 'missed');

        return $updated;
    }

    /**
     * Annule un rendez-vous accepté et en crée un nouveau (accepted)
     * sur le prochain créneau libre du même médecin.
     *
     * @return Appointment|null Le nouveau rendez-vous, ou null si aucun créneau.
     */
    public function reschedule(Appointment $appointment): ?Appointment
    {
        $newAppointment = DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment);

            if ($appointment->status !== Appointment::STATUS_ACCEPTED) {
                throw new AppointmentException('Seuls les rendez-vous confirmés peuvent être reportés.');
            }

            $appointment->load('availability');
            $oldSlot = $appointment->availability;

            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
            $this->releaseAvailabilityIfFree($appointment->availability_id);

            $query = Availability::query()
                ->where('doctor_id', $appointment->doctor_id)
                ->where('is_available', true)
                ->where('date', '>=', now()->toDateString());

            if ($appointment->availability_id) {
                $query->whereKeyNot($appointment->availability_id);
            }

            if ($oldSlot) {
                $slotDate = $oldSlot->date->format('Y-m-d');
                $slotTime = substr((string) $oldSlot->start_time, 0, 8);
                $query->where(function ($q) use ($slotDate, $slotTime) {
                    $q->where('date', '>', $slotDate)
                        ->orWhere(function ($q2) use ($slotDate, $slotTime) {
                            $q2->where('date', $slotDate)
                                ->where('start_time', '>', $slotTime);
                        });
                });
            }

            $nextSlot = $query->orderBy('date')->orderBy('start_time')->lockForUpdate()->first();

            if (! $nextSlot) {
                return null;
            }

            if ($this->hasOccupyingAppointment($nextSlot->id)) {
                return null;
            }

            $nextSlot->update(['is_available' => false]);

            return Appointment::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'availability_id' => $nextSlot->id,
                'appointment_date' => $nextSlot->date->format('Y-m-d').' '.$nextSlot->start_time,
                'status' => Appointment::STATUS_ACCEPTED,
                'notes' => $appointment->notes,
            ]);
        });

        $oldAppointment = $appointment->fresh();

        if ($newAppointment) {
            $this->notifier->notifyRescheduled($oldAppointment, $newAppointment);
        } else {
            $this->notifier->notifyExpired($oldAppointment, 'cancelled');
        }

        return $newAppointment;
    }

    /**
     * Applique un statut arbitraire (admin). Conserve la réactivation
     * cancelled/rejected → statut occupant, et libère le slot sinon.
     */
    public function applyAdminStatus(Appointment $appointment, string $newStatus): Appointment
    {
        $allowed = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_ACCEPTED,
            Appointment::STATUS_REJECTED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_MISSED,
        ];

        if (! in_array($newStatus, $allowed, true)) {
            throw new AppointmentException('Le statut sélectionné est invalide.');
        }

        $outcome = DB::transaction(function () use ($appointment, $newStatus) {
            $appointment = $this->lockAppointment($appointment);
            $oldStatus = $appointment->status;

            $appointment->update(['status' => $newStatus]);

            $wasOccupying = in_array($oldStatus, Appointment::OCCUPYING_STATUSES, true);
            $isOccupying = in_array($newStatus, Appointment::OCCUPYING_STATUSES, true);

            if ($wasOccupying && ! $isOccupying) {
                $this->releaseAvailabilityIfFree($appointment->availability_id);
            }

            if (! $wasOccupying && $isOccupying && $appointment->availability_id) {
                Availability::query()
                    ->lockForUpdate()
                    ->whereKey($appointment->availability_id)
                    ->update(['is_available' => false]);
            }

            return [
                'old' => $oldStatus,
                'appointment' => $appointment->refresh(),
            ];
        });

        $this->notifier->notifyAdminTransition($outcome['old'], $outcome['appointment']);

        return $outcome['appointment'];
    }

    /**
     * Libère le créneau si plus aucun rendez-vous occupant n'y est lié.
     *
     * Occupants : pending, accepted, completed, missed.
     * Non occupants : cancelled, rejected.
     */
    private function releaseAvailabilityIfFree(?int $availabilityId): void
    {
        if (! $availabilityId) {
            return;
        }

        $availability = Availability::query()
            ->lockForUpdate()
            ->find($availabilityId);

        if (! $availability) {
            return;
        }

        if (! $this->hasOccupyingAppointment($availability->id)) {
            $availability->update(['is_available' => true]);
        }
    }

    private function hasOccupyingAppointment(int $availabilityId): bool
    {
        return Appointment::query()
            ->where('availability_id', $availabilityId)
            ->whereIn('status', Appointment::OCCUPYING_STATUSES)
            ->exists();
    }

    private function lockAppointment(Appointment $appointment): Appointment
    {
        $locked = Appointment::query()
            ->lockForUpdate()
            ->find($appointment->id);

        if (! $locked) {
            throw new AppointmentException('Rendez-vous introuvable.');
        }

        return $locked;
    }

    private function assertTransition(Appointment $appointment, string $from, string $actionLabel): void
    {
        if ($appointment->status !== $from) {
            throw new AppointmentException("Ce rendez-vous ne peut pas être {$actionLabel}.");
        }
    }
}
