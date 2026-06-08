<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des utilisateurs côté administrateur.
 *
 * Permet à l'administrateur de lister, créer, modifier et supprimer
 * les comptes utilisateurs, avec filtrage par rôle.
 */
class UserController extends Controller
{
    /**
     * Affiche la liste paginée des utilisateurs, avec filtre optionnel par rôle.
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $role = $request->query('role');

        $allowedSorts = ['name', 'created_at'];
        $sort      = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $users = User::with('doctor')
            ->when(
                $role && in_array($role, ['patient', 'doctor', 'admin']),
                fn ($q) => $q->where('role', $role)
            )
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        // Comptes médecins en attente de validation (rôle doctor, pas encore de profil)
        $pendingDoctors = User::doctors()
            ->doesntHave('doctor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', compact('users', 'role', 'pendingDoctors'));
    }

    /**
     * Affiche le formulaire de création d'un utilisateur.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Enregistre un nouvel utilisateur.
     *
     * Le mot de passe est hashé avant l'insertion.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(['patient', 'doctor', 'admin'])],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur.
     *
     * @param  User $user
     * @return View
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * L'unicité de l'email ignore l'utilisateur courant pour éviter
     * un faux conflit lors de la sauvegarde sans changement d'email.
     *
     * @param  Request $request
     * @param  User    $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['patient', 'doctor', 'admin'])],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprime (soft-delete) un utilisateur.
     *
     * Protection : un administrateur ne peut pas supprimer son propre compte.
     *
     * @param  User    $user
     * @param  Request $request
     * @return RedirectResponse
     */
    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
