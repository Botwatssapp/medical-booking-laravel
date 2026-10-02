<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Policy de contrôle d'accès pour les rendez-vous.
 *
 * L'appartenance (patient / médecin / admin) est gérée ici.
 * Les transitions de statut (pending → accepted, etc.) sont
 * appliquées par AppointmentService.
 */
class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user)
            || $this->isOwnerPatient($user, $appointment)
            || $this->isOwnerDoctor($user, $appointment);
    }

    /**
     * Mise à jour des notes / champs non statutaires.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $this->view($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->cancel($user, $appointment);
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user)
            || $this->isOwnerPatient($user, $appointment)
            || $this->isOwnerDoctor($user, $appointment);
    }

    public function accept(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isOwnerDoctor($user, $appointment);
    }

    public function reject(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isOwnerDoctor($user, $appointment);
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isOwnerDoctor($user, $appointment);
    }

    public function markMissed(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isOwnerDoctor($user, $appointment);
    }

    public function reschedule(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isOwnerDoctor($user, $appointment);
    }

    public function applyAdminStatus(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    private function isOwnerPatient(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->patient_id;
    }

    private function isOwnerDoctor(User $user, Appointment $appointment): bool
    {
        return $appointment->doctor !== null
            && $user->id === $appointment->doctor->user_id;
    }
}
