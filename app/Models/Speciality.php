<?php

namespace App\Models;

use Database\Factories\SpecialityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une spécialité médicale.
 *
 * Une spécialité regroupe des médecins exerçant dans le même domaine.
 * Exemples : Cardiologie, Pédiatrie, Dermatologie.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 */
class Speciality extends Model
{
    /** @use HasFactory<SpecialityFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    // =========================================================================
    // Relations Eloquent
    // =========================================================================

    /**
     * Médecins appartenant à cette spécialité.
     *
     * Une spécialité peut avoir plusieurs médecins.
     *
     * @return HasMany<Doctor, $this>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
