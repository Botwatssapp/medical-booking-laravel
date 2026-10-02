<?php

namespace App\Services;

use App\Exceptions\DoctorException;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur d'onboarding et de profil médical.
 *
 * La « validation » admin = création de la ligne `doctors`.
 * Il n'existe pas de champ approved/validated/is_active.
 *
 * Ne gère ni l'auth, ni les rendez-vous, ni les disponibilités.
 */
class DoctorOnboardingService
{
    /**
     * Crée le profil médical d'un compte déjà en rôle médecin.
     *
     * C'est l'étape de confirmation admin : sans cette ligne,
     * le médecin n'est pas opérationnel.
     *
     * @param  array{phone?: string|null, address?: string|null, bio?: string|null, photo?: string|null}  $attributes
     */
    public function createProfile(User $user, int $specialityId, array $attributes = []): Doctor
    {
        return DB::transaction(function () use ($user, $specialityId, $attributes) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->isDoctor()) {
                throw new DoctorException('Seuls les comptes de rôle médecin peuvent recevoir un profil médical.');
            }

            if (Doctor::withTrashed()->where('user_id', $locked->id)->exists()) {
                throw new DoctorException('Cet utilisateur est déjà enregistré comme médecin.');
            }

            $this->assertSpecialityExists($specialityId);

            $payload = $this->onlyProfileFields($attributes);

            return Doctor::create([
                'user_id' => $locked->id,
                'speciality_id' => $specialityId,
                'phone' => $payload['phone'] ?? null,
                'address' => $payload['address'] ?? null,
                'bio' => $payload['bio'] ?? null,
                'photo' => $payload['photo'] ?? null,
            ]);
        });
    }

    /**
     * Mise à jour admin : spécialité et infos professionnelles.
     * `user_id` n'est jamais réassigné.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateByAdmin(Doctor $doctor, array $attributes): Doctor
    {
        $payload = $this->onlyProfileFields($attributes);

        if (array_key_exists('speciality_id', $attributes) && $attributes['speciality_id'] !== null) {
            $specialityId = (int) $attributes['speciality_id'];
            $this->assertSpecialityExists($specialityId);
            $payload['speciality_id'] = $specialityId;
        }

        unset($payload['user_id']);

        $doctor->update($payload);

        return $doctor->refresh();
    }

    /**
     * Mise à jour par le médecin propriétaire : phone / address / bio uniquement.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateOwnProfile(Doctor $doctor, User $actor, array $attributes): Doctor
    {
        if ($actor->doctor === null || $actor->doctor->id !== $doctor->id) {
            throw new DoctorException('Vous n\'êtes pas autorisé à modifier ce profil médecin.');
        }

        $payload = array_intersect_key($attributes, array_flip(['phone', 'address', 'bio']));

        $doctor->update($payload);

        return $doctor->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function onlyProfileFields(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(['phone', 'address', 'bio', 'photo']));
    }

    private function assertSpecialityExists(int $specialityId): void
    {
        if (! Speciality::query()->whereKey($specialityId)->exists()) {
            throw new DoctorException('La spécialité sélectionnée n\'existe pas.');
        }
    }
}
