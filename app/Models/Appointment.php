<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
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
 * @property int $id
 * @property int $patient_id
 * @property int $doctor_id
 * @property int|null $availability_id
 * @property Carbon $appointment_date
 * @property string $status
 * @property string|null $notes
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Statuts possibles d'un rendez-vous.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_MISSED = 'missed';

    /**
     * Statuts qui occupent encore le créneau de disponibilité.
     *
     * `cancelled` et `rejected` sont les seuls statuts qui libèrent le slot.
     *
     * @var list<string>
     */
    public const OCCUPYING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_COMPLETED,
        self::STATUS_MISSED,
    ];

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
     * Colonne générée d'intégrité SQL — pas un champ métier.
     *
     * @var list<string>
     */
    protected $hidden = [
        'occupying_availability_id',
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
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filtre les rendez-vous acceptés.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Filtre les rendez-vous rejetés.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Filtre les rendez-vous annulés.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Filtre les rendez-vous terminés.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Filtre les rendez-vous manqués.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
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
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>', now());
    }

    /**
     * Filtre les rendez-vous passés (avant maintenant).
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now());
    }
}
