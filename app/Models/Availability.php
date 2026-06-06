<?php

namespace App\Models;

use Database\Factories\AvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un créneau de disponibilité d'un médecin.
 *
 * Chaque disponibilité définit une plage horaire (start_time → end_time)
 * sur une date donnée. Le champ `is_available` indique si le créneau
 * peut encore être réservé.
 *
 * @property int    $id
 * @property int    $doctor_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property bool   $is_available
 */
class Availability extends Model
{
    /** @use HasFactory<AvailabilityFactory> */
    use HasFactory;

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    /**
     * Casts automatiques des attributs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'         => 'date',
        'is_available' => 'boolean',
    ];

    // =========================================================================
    // Relations Eloquent
    // =========================================================================

    /**
     * Médecin propriétaire de ce créneau de disponibilité.
     *
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Rendez-vous liés à ce créneau de disponibilité.
     *
     * Un créneau peut être associé à un rendez-vous.
     *
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Filtre les créneaux disponibles à partir d'aujourd'hui.
     *
     * Correction : remplace `now()->date()` (inexistant) par `now()->toDateString()`.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->where('date', '>=', now()->toDateString());
    }

    /**
     * Filtre les créneaux d'un médecin spécifique.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @param  int                                           $doctorId
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Filtre les créneaux par date exacte.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @param  string                                        $date
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Filtre les créneaux dans une plage de dates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static> $query
     * @param  string                                        $startDate
     * @param  string                                        $endDate
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
