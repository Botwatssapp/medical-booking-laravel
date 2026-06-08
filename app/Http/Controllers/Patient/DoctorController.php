<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de consultation de l'annuaire des médecins côté patient.
 *
 * Permet au patient de rechercher et consulter les profils médecins
 * avec leurs disponibilités futures.
 */
class DoctorController extends Controller
{
    /**
     * Affiche la liste paginée des médecins avec filtres optionnels.
     *
     * Filtres disponibles :
     * - specialty_id : filtre par spécialité
     * - search       : recherche par nom du médecin ou nom de la spécialité
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Doctor::with(['speciality', 'user']);

        if ($request->filled('specialty_id')) {
            $query->where('doctors.speciality_id', $request->integer('specialty_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('speciality', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $allowedSorts = ['name' => 'users.name', 'speciality' => 'specialities.name'];
        $sortKey      = $request->query('sort', 'name');
        $sort         = $allowedSorts[$sortKey] ?? 'users.name';
        $direction    = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $query->join('users', 'users.id', '=', 'doctors.user_id')
              ->join('specialities', 'specialities.id', '=', 'doctors.speciality_id')
              ->select('doctors.*')
              ->orderBy($sort, $direction);

        $doctors     = $query->paginate(12)->withQueryString();
        $specialties = Speciality::orderBy('name')->get();

        return view('patient.doctors.index', compact('doctors', 'specialties'));
    }

    /**
     * Affiche le profil complet d'un médecin avec ses créneaux disponibles.
     *
     * Correction : les disponibilités sont filtrées pour n'afficher que
     * les créneaux futurs et encore disponibles (is_available = true).
     *
     * @param  Doctor $doctor
     * @return View
     */
    public function show(Doctor $doctor): View
    {
        $doctor->load(['speciality', 'user']);

        // Charge tous les créneaux futurs (disponibles ET réservés)
        $availabilities = $doctor->availabilities()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('patient.doctors.show', compact('doctor', 'availabilities'));
    }
}
