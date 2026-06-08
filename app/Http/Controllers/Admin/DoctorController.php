<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des médecins côté administrateur.
 *
 * Permet à l'administrateur de lister, créer, modifier et supprimer
 * les profils médecins avec leurs informations personnelles.
 */
class DoctorController extends Controller
{
    /**
     * Affiche la liste paginée des médecins avec leur spécialité.
     *
     * Utilise l'eager loading pour éviter les requêtes N+1.
     *
     * @return View
     */
    public function index(\Illuminate\Http\Request $request): View
    {
        $allowedSorts = ['name' => 'users.name', 'speciality' => 'specialities.name'];
        $sortKey   = $request->query('sort', 'name');
        $sort      = $allowedSorts[$sortKey] ?? 'users.name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $doctors = Doctor::with(['user', 'speciality'])
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->join('specialities', 'specialities.id', '=', 'doctors.speciality_id')
            ->select('doctors.*')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    /**
     * Affiche le formulaire de création d'un médecin.
     *
     * Ne liste que les utilisateurs de rôle 'doctor' sans profil existant.
     *
     * @return View
     */
    public function create(): View
    {
        $users      = User::doctors()->doesntHave('doctor')->orderBy('name')->get();
        $specialties = Speciality::orderBy('name')->get();

        return view('admin.doctors.create', compact('users', 'specialties'));
    }

    /**
     * Enregistre un nouveau profil médecin.
     *
     * Gère l'upload de photo et la création du profil.
     *
     * @param  StoreDoctorRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('doctors', 'public');
        }

        Doctor::create($data);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin créé avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'un médecin.
     *
     * Inclut dans la liste des utilisateurs le propriétaire actuel du profil.
     *
     * @param  Doctor $doctor
     * @return View
     */
    public function edit(Doctor $doctor): View
    {
        $users = User::doctors()
            ->where(function ($q) use ($doctor) {
                $q->doesntHave('doctor')
                  ->orWhereHas('doctor', fn ($q2) => $q2->where('id', $doctor->id));
            })
            ->orderBy('name')
            ->get();

        $specialties = Speciality::orderBy('name')->get();

        return view('admin.doctors.edit', compact('doctor', 'users', 'specialties'));
    }

    /**
     * Met à jour le profil d'un médecin.
     *
     * Supprime l'ancienne photo du storage avant d'enregistrer la nouvelle.
     *
     * @param  UpdateDoctorRequest $request
     * @param  Doctor              $doctor
     * @return RedirectResponse
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Suppression de l'ancienne photo pour éviter les fichiers orphelins
            if ($doctor->photo) {
                Storage::disk('public')->delete($doctor->photo);
            }
            $data['photo'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($data);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin mis à jour avec succès.');
    }

    /**
     * Supprime (soft-delete) un profil médecin.
     *
     * @param  Doctor $doctor
     * @return RedirectResponse
     */
    public function destroy(Doctor $doctor): RedirectResponse
    {
        $doctor->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin supprimé avec succès.');
    }
}
