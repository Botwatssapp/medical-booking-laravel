<?php

namespace App\Policies;

use App\Models\Availability;
use App\Models\User;

/**
 * Autorisation des créneaux de disponibilité.
 *
 * Les patients ne gèrent pas les disponibilités.
 * Un médecin ne gère que les siennes.
 * L'admin n'a actuellement aucune route de gestion des créneaux :
 * le middleware `doctor` bloque déjà l'accès aux URLs médecin.
 */
class AvailabilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDoctorProfile();
    }

    public function view(User $user, Availability $availability): bool
    {
        return $this->owns($user, $availability);
    }

    public function create(User $user): bool
    {
        return $user->hasDoctorProfile();
    }

    public function update(User $user, Availability $availability): bool
    {
        return false;
    }

    public function delete(User $user, Availability $availability): bool
    {
        return $this->owns($user, $availability);
    }

    private function owns(User $user, Availability $availability): bool
    {
        return $user->hasDoctorProfile()
            && $user->doctor->id === $availability->doctor_id;
    }
}
