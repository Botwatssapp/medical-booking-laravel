<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Availability;

Route::get('/doctor/{doctorId}/availabilities', function ($doctorId) {
    return Availability::where('doctor_id', $doctorId)
        ->where('is_available', true)
        ->where('date', '>=', today())
        ->orderBy('date')
        ->orderBy('start_time')
        ->get(['id', 'date', 'start_time', 'end_time']);
})->name('api.availabilities');
