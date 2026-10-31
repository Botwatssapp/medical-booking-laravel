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
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role');
        if (! in_array($role, ['patient', 'doctor', 'admin'], true)) {
            $role = null;
        }

        $status = $request->query('status');
        if (! in_array($status, ['pending', 'validated'], true)) {
            $status = null;
        }

        $registered = $request->query('registered');
        if (! is_string($registered) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $registered)) {
            $registered = null;
        }

        $allowedSorts = ['name', 'created_at'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $users = User::query()
            ->with('doctor')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($status === 'pending', function ($query) {
                $query->where('role', 'doctor')->doesntHave('doctor');
            })
            ->when($status === 'validated', function ($query) {
                $query->where('role', 'doctor')->has('doctor');
            })
            ->when($registered, fn ($query) => $query->whereDate('created_at', $registered))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $pendingDoctors = User::doctors()
            ->doesntHave('doctor')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => User::query()->count(),
            'patients' => User::patients()->count(),
            'doctors' => User::doctors()->count(),
            'admins' => User::admins()->count(),
            'pending' => $pendingDoctors->count(),
            'validatedDoctors' => User::doctors()->has('doctor')->count(),
        ];

        $filters = [
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'registered' => $registered,
            'sort' => $sort,
            'direction' => $direction,
        ];

        return view('admin.users.index', compact('users', 'role', 'pendingDoctors', 'stats', 'filters'));
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
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(['patient', 'doctor', 'admin'])],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        if ($request->boolean('add_another')) {
            return redirect()->route('admin.users.create')
                ->with('success', 'Utilisateur créé avec succès.');
        }

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
        $user->load('doctor.speciality');
        $appointmentCount = $user->isPatient()
            ? $user->appointments()->count()
            : 0;

        return view('admin.users.edit', compact('user', 'appointmentCount'));
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:30'],
            'role'     => ['required', Rule::in(['patient', 'doctor', 'admin'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

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
