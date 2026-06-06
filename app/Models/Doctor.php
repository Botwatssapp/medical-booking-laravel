<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'user_id', 'speciality_id', 'phone', 'address', 'bio', 'is_available'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
