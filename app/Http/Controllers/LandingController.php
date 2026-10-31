<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $specialties = Speciality::query()
            ->withCount('doctors')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $doctors = Doctor::query()
            ->with([
                'user',
                'speciality',
                'availabilities' => fn ($q) => $q->available()
                    ->where('date', '<=', now()->addDays(14)->toDateString())
                    ->orderBy('date')
                    ->orderBy('start_time'),
            ])
            ->withCount([
                'availabilities as available_slots_count' => fn ($q) => $q->available(),
            ])
            ->orderByDesc('available_slots_count')
            ->limit(6)
            ->get();

        return view('landing', [
            'specialties' => $specialties,
            'doctors' => $doctors,
            'doctorCount' => Doctor::query()->count(),
            'specialtyCount' => Speciality::query()->count(),
            'appointmentCount' => Appointment::query()->count(),
        ]);
    }
}
