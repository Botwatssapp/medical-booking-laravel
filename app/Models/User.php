<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Modèle représentant un utilisateur du système.
 *
 * Un utilisateur peut être un patient, un médecin ou un administrateur.
 * Le rôle détermine les permissions et l'interface disponible.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $role  patient|doctor|admin
 * @property string $password
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image',
        'phone',
        'gender',
        'birth_date',
        'blood_type',
        'weight',
        'height',
        'address',
        'emergency_contact',
    ];

    /**
     * Attributs cachés lors de la sérialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automatiques des attributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'birth_date'        => 'date',
            'weight'            => 'decimal:2',
            'height'            => 'decimal:2',
        ];
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * URL publique de la photo de profil.
     * Retourne l'URL du fichier stocké, ou null si aucune image.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image
            ? Storage::disk('public')->url($this->profile_image)
            : null;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getGenderLabelAttribute(): ?string
    {
        return match($this->gender) {
            'male'   => 'Homme',
            'female' => 'Femme',
            'other'  => 'Autre',
            default  => null,
        };
    }

    // =========================================================================
    // Relations Eloquent
    // =========================================================================

    /**
     * Profil médecin associé à l'utilisateur.
     *
     * Un utilisateur de rôle 'doctor' possède un profil médecin.
     *
     * @return HasOne<Doctor, $this>
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Liste des rendez-vous du patient.
     *
     * Relation HasMany via la colonne patient_id dans la table appointments.
     *
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Filtre les utilisateurs ayant le rôle patient.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePatients($query)
    {
        return $query->where('role', 'patient');
    }

    /**
     * Filtre les utilisateurs ayant le rôle médecin.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeDoctors($query)
    {
        return $query->where('role', 'doctor');
    }

    /**
     * Filtre les utilisateurs ayant le rôle administrateur.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // =========================================================================
    // Helpers de rôle
    // =========================================================================

    /**
     * Vérifie si l'utilisateur est un administrateur.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est un médecin.
     *
     * @return bool
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Vérifie si l'utilisateur est un patient.
     *
     * @return bool
     */
    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }
}
