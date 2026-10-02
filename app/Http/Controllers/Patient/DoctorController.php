<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Annuaire des médecins validés, côté patient.
 */
class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::query()
            ->whereHas('user', fn ($q) => $q->where('role', 'doctor'))
            ->with(['speciality', 'user'])
            ->withCount([
                'availabilities as available_slots_count' => fn ($q) => $q->available(),
            ]);

        $specialityFilter = $request->input('speciality', $request->input('specialty_id'));

        if (filled($specialityFilter)) {
            if (ctype_digit((string) $specialityFilter)) {
                $query->where('doctors.speciality_id', (int) $specialityFilter);
            } else {
                $query->whereHas('speciality', fn ($q) => $q->where('name', $specialityFilter));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('speciality', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $allowedSorts = ['name' => 'users.name', 'speciality' => 'specialities.name'];
        $sortKey = $request->query('sort', 'name');
        $sort = $allowedSorts[$sortKey] ?? 'users.name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $query->join('users', 'users.id', '=', 'doctors.user_id')
            ->join('specialities', 'specialities.id', '=', 'doctors.speciality_id')
            ->select('doctors.*')
            ->orderBy($sort, $direction);

        $doctors = $query->paginate(12)->withQueryString();
        $specialties = Speciality::query()->orderBy('name')->get();
        $hasFilters = $request->filled('search')
            || $request->filled('speciality')
            || $request->filled('specialty_id');

        return view('patient.doctors.index', compact('doctors', 'specialties', 'hasFilters'));
    }

    public function show(Doctor $doctor): View
    {
        $this->authorize('view', $doctor);

        abort_unless($doctor->user?->isDoctor(), 404);

        $doctor->load(['speciality', 'user']);

        $availabilities = $doctor->availabilities()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('patient.doctors.show', compact('doctor', 'availabilities'));
    }
}
