<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Policy de contrôle d'accès pour les rendez-vous.
 *
 * Définit les règles d'autorisation selon le rôle de l'utilisateur :
 * - Administrateur : accès complet
 * - Médecin : accès à ses propres rendez-vous
 * - Patient : accès à ses propres rendez-vous uniquement
 */
class AppointmentPolicy
{
    /**
     * Détermine si l'utilisateur peut voir un rendez-vous.
     *
     * Autorisé si :
     * - L'utilisateur est le patient du rendez-vous
     * - L'utilisateur est le médecin du rendez-vous
     * - L'utilisateur est administrateur
     *
     * Correction : ajout d'un null-check sur `$appointment->doctor`
     * pour éviter les erreurs lorsque le médecin est soft-deleted.
     *
     * @param  User        $user
     * @param  Appointment $appointment
     * @return bool
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $appointment->patient_id) {
            return true;
        }

        // Null-check car le médecin peut avoir été soft-deleted
        if ($appointment->doctor && $user->id === $appointment->doctor->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut modifier un rendez-vous.
     *
     * Le patient peut modifier (annuler) ses propres rendez-vous futurs.
     * Le médecin peut changer le statut de ses propres rendez-vous.
     * L'administrateur a accès complet.
     *
     * @param  User        $user
     * @param  Appointment $appointment
     * @return bool
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $appointment->patient_id) {
            return true;
        }

        if ($appointment->doctor && $user->id === $appointment->doctor->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut annuler (supprimer) un rendez-vous.
     *
     * Seuls le patient concerné, le médecin concerné et l'administrateur
     * peuvent annuler un rendez-vous.
     *
     * @param  User        $user
     * @param  Appointment $appointment
     * @return bool
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $appointment->patient_id) {
            return true;
        }

        if ($appointment->doctor && $user->id === $appointment->doctor->user_id) {
            return true;
        }

        return false;
    }
}
