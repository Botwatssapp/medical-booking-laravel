<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

/**
 * Autorisation des profils médecins.
 *
 * La validation (création du profil) est réservée à l'admin.
 * Un médecin ne gère que son propre profil.
 * Les patients ont un accès lecture via l'annuaire (middleware `patient`).
 */
class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPatient() || $user->hasDoctorProfile();
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->isAdmin()
            || $user->isPatient()
            || $this->owns($user, $doctor);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->isAdmin() || $this->owns($user, $doctor);
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->isAdmin();
    }

    private function owns(User $user, Doctor $doctor): bool
    {
        return $user->hasDoctorProfile()
            && $user->doctor->id === $doctor->id;
    }
}
