<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des spécialités médicales côté administrateur.
 *
 * Permet à l'administrateur de gérer le référentiel des spécialités
 * (cardiologie, pédiatrie, etc.) utilisées pour catégoriser les médecins.
 */
class SpecialtyController extends Controller
{
    /**
     * Affiche la liste paginée des spécialités.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $filter = $request->query('filter');
        if (! in_array($filter, ['with', 'empty'], true)) {
            $filter = null;
        }

        $sortParam = (string) $request->query('sort', 'name');
        if ($sortParam === 'name' && $request->query('direction') === 'desc') {
            $sortParam = 'name_desc';
        }

        $sortMap = [
            'name' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'doctors' => ['doctors_count', 'asc'],
            'doctors_desc' => ['doctors_count', 'desc'],
            'created' => ['created_at', 'desc'],
        ];
        [$sortColumn, $direction] = $sortMap[$sortParam] ?? ['name', 'asc'];
        if (! isset($sortMap[$sortParam])) {
            $sortParam = 'name';
        }

        $specialties = Speciality::query()
            ->withCount('doctors')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($filter === 'with', fn ($query) => $query->has('doctors'))
            ->when($filter === 'empty', fn ($query) => $query->doesntHave('doctors'))
            ->orderBy($sortColumn, $direction)
            ->paginate(15)
            ->withQueryString();

        $total = Speciality::query()->count();
        $withDoctors = Speciality::query()->has('doctors')->count();
        $withoutDoctors = Speciality::query()->doesntHave('doctors')->count();
        $doctorCount = Doctor::query()->count();

        $stats = [
            'total' => $total,
            'withDoctors' => $withDoctors,
            'withoutDoctors' => $withoutDoctors,
            'doctors' => $doctorCount,
            'avgPerSpecialty' => $total > 0 ? round($doctorCount / $total, 1) : 0,
            'coverage' => $total > 0 ? (int) round(($withDoctors / $total) * 100) : 0,
        ];

        $filters = [
            'search' => $search,
            'filter' => $filter,
            'sort' => $sortParam,
            'direction' => $direction,
        ];

        return view('admin.specialties.index', compact('specialties', 'stats', 'filters'));
    }

    /**
     * Affiche le formulaire de création d'une spécialité.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.specialties.create');
    }

    /**
     * Enregistre une nouvelle spécialité médicale.
     *
     * La validation de l'unicité du nom est gérée dans StoreSpecialtyRequest.
     *
     * @param  StoreSpecialtyRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        Speciality::create($request->validated());

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Spécialité créée avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'une spécialité.
     *
     * @param  Speciality $specialty
     * @return View
     */
    public function edit(Speciality $specialty): View
    {
        $specialty->load(['doctors.user']);

        $doctorIds = $specialty->doctors->pluck('id');

        $appointmentCount = $doctorIds->isEmpty()
            ? 0
            : Appointment::query()->whereIn('doctor_id', $doctorIds)->count();

        $monthAppointmentCount = $doctorIds->isEmpty()
            ? 0
            : Appointment::query()
                ->whereIn('doctor_id', $doctorIds)
                ->whereBetween('appointment_date', [
                    now()->copy()->startOfMonth(),
                    now()->copy()->endOfMonth(),
                ])
                ->count();

        return view('admin.specialties.edit', compact(
            'specialty',
            'appointmentCount',
            'monthAppointmentCount'
        ));
    }

    /**
     * Met à jour une spécialité existante.
     *
     * La validation exclut la spécialité actuelle lors du contrôle d'unicité.
     *
     * @param  UpdateSpecialtyRequest $request
     * @param  Speciality             $specialty
     * @return RedirectResponse
     */
    public function update(UpdateSpecialtyRequest $request, Speciality $specialty): RedirectResponse
    {
        $specialty->update($request->validated());

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Spécialité mise à jour avec succès.');
    }

    /**
     * Supprime (soft-delete) une spécialité.
     *
     * @param  Speciality $specialty
     * @return RedirectResponse
     */
    public function destroy(Speciality $specialty): RedirectResponse
    {
        $specialty->delete();

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Spécialité supprimée avec succès.');
    }
}
