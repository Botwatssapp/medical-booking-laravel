<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    // Dashboard Médecin
    public function dashboard()
    {
        $appointments = Appointment::with('patient')
            ->where('doctor_id', Auth::user()->doctor->id)
            ->orderBy('date', 'desc')
            ->get();

        $stats = [
            'total'     => $appointments->count(),
            'pending'   => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'refused'   => $appointments->where('status', 'refused')->count(),
        ];

        return view('doctor.dashboard', compact('appointments', 'stats'));
    }

    // Confirmer RDV
    public function confirmAppointment(Appointment $appointment)
    {
        $appointment->update(['status' => 'confirmed']);
        return back()->with('success', 'Rendez-vous confirmé');
    }

    // Refuser RDV
    public function refuseAppointment(Appointment $appointment)
    {
        $appointment->update(['status' => 'refused']);
        return back()->with('success', 'Rendez-vous refusé');
    }

    // Gérer disponibilités
    public function profile()
    {
        $doctor = Auth::user()->doctor;
        return view('doctor.profile', compact('doctor'));
    }

    // Update profil
    public function updateProfile(Request $request)
    {
        $request->validate([
            'phone'   => 'nullable|string',
            'address' => 'nullable|string',
            'bio'     => 'nullable|string|max:500',
        ]);

        Auth::user()->doctor->update($request->only('phone', 'address', 'bio'));

        return back()->with('success', 'Profil mis à jour');
    }
}
