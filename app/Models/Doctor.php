<?php

namespace App\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant le profil d'un médecin.
 *
 * Un médecin est lié à un utilisateur (User) de rôle 'doctor'
 * et appartient à une spécialité médicale.
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $speciality_id
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $bio
 * @property string|null $photo
 */
class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'speciality_id',
        'phone',
        'address',
        'bio',
        'photo',
    ];

    // =========================================================================
    // Relations Eloquent
    // =========================================================================

    /**
     * Utilisateur propriétaire du profil médecin.
     *
     * Chaque médecin est lié à un compte utilisateur unique.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Spécialité médicale du médecin.
     *
     * Un médecin appartient à une seule spécialité.
     *
     * @return BelongsTo<Speciality, $this>
     */
    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }

    /**
     * Créneaux de disponibilité du médecin.
     *
     * Un médecin peut avoir plusieurs créneaux de disponibilité.
     *
     * @return HasMany<Availability, $this>
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Rendez-vous du médecin.
     *
     * Un médecin peut avoir plusieurs rendez-vous avec différents patients.
     *
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
