<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Speciality;
use Illuminate\Http\RedirectResponse;
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
    public function index(\Illuminate\Http\Request $request): View
    {
        $direction   = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $specialties = Speciality::orderBy('name', $direction)->paginate(15)->withQueryString();

        return view('admin.specialties.index', compact('specialties'));
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
        return view('admin.specialties.edit', compact('specialty'));
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
