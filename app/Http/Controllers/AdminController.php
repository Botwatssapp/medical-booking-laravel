<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Dashboard Admin
    public function dashboard()
    {
        $stats = [
            'patients'     => User::where('role', 'patient')->count(),
            'doctors'      => User::where('role', 'doctor')->count(),
            'appointments' => Appointment::count(),
            'pending'      => Appointment::where('status', 'pending')->count(),
            'confirmed'    => Appointment::where('status', 'confirmed')->count(),
            'cancelled'    => Appointment::where('status', 'cancelled')->count(),
        ];

        $appointments = Appointment::with('patient', 'doctor.user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'appointments'));
    }

    // Liste Users
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    // Supprimer User
    public function deleteUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé');
    }

    // Liste Médecins
    public function doctors()
    {
        $doctors = Doctor::with('user', 'speciality')->get();
        return view('admin.doctors', compact('doctors'));
    }

    // Ajouter Médecin
    public function storeDoctor(Request $request)
    {
        $request->validate([
            'name'          => 'required|string',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8',
            'speciality_id' => 'required|exists:specialities,id',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'doctor',
        ]);

        Doctor::create([
            'user_id'       => $user->id,
            'speciality_id' => $request->speciality_id,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return back()->with('success', 'Médecin ajouté avec succès');
    }

    // Supprimer Médecin
    public function deleteDoctor(Doctor $doctor)
    {
        $doctor->user->delete();
        return back()->with('success', 'Médecin supprimé');
    }

    // Liste Spécialités
    public function specialities()
    {
        $specialities = Speciality::all();
        return view('admin.specialities', compact('specialities'));
    }

    // Ajouter Spécialité
    public function storeSpeciality(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|unique:specialities',
            'description' => 'nullable|string',
        ]);

        Speciality::create($request->only('name', 'description'));
        return back()->with('success', 'Spécialité ajoutée');
    }

    // Supprimer Spécialité
    public function deleteSpeciality(Speciality $speciality)
    {
        $speciality->delete();
        return back()->with('success', 'Spécialité supprimée');
    }

    // Tous les RDV
    public function appointments()
    {
        $appointments = Appointment::with('patient', 'doctor.user')
            ->latest()
            ->get();
        return view('admin.appointments', compact('appointments'));
    }
}
