<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un rendez-vous médical.
 *
 * Un rendez-vous lie un patient à un médecin sur un créneau de disponibilité.
 * Le statut évolue : pending → accepted/rejected → completed/missed/cancelled.
 *
 * @property int         $id
 * @property int         $patient_id
 * @property int         $doctor_id
 * @property int|null    $availability_id
 * @property \Carbon\Carbon $appointment_date
 * @property string      $status
 * @property string|null $notes
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Statuts possibles d'un rendez-vous.
     */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED    = 'missed';

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'availability_id',
        'appointment_date',
        'status',
        'notes',
    ];

    /**
     * Casts automatiques des attributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
        ];
    }

    // =========================================================================
    // Relations Eloquent
    // =========================================================================

    /**
     * Patient ayant réservé ce rendez-vous.
     *
     * Clé étrangère explicite `patient_id` car la colonne ne suit pas
     * la convention `user_id`.
     *
     * @return BelongsTo<User, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Médecin concerné par ce rendez-vous.
     *
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Créneau de disponibilité associé à ce rendez-vous.
     *
     * Nullable : peut être null si le créneau a été supprimé.
     *
     * @return BelongsTo<Availability, $this>
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    // =========================================================================
    // Query Scopes — statuts
    // =========================================================================

    /**
     * Filtre les rendez-vous en attente de confirmation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filtre les rendez-vous acceptés.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Filtre les rendez-vous rejetés.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Filtre les rendez-vous annulés.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Filtre les rendez-vous terminés.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Filtre les rendez-vous manqués.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeMissed($query)
    {
        return $query->where('status', self::STATUS_MISSED);
    }

    // =========================================================================
    // Query Scopes — temporels
    // =========================================================================

    /**
     * Filtre les rendez-vous futurs (après maintenant).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>', now());
    }

    /**
     * Filtre les rendez-vous passés (avant maintenant).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now());
    }
}
