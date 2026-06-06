<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}
