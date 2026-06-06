<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRefused;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DoctorController extends Controller
{
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

    // ✅ Confirmer — صيفط email
    public function confirmAppointment(Appointment $appointment)
    {
        $appointment->update(['status' => 'confirmed']);

        Mail::to($appointment->patient->email)
            ->send(new AppointmentConfirmed($appointment));

        return back()->with('success', 'Rendez-vous confirmé — Email envoyé');
    }

    // ✅ Refuser — صيفط email
    public function refuseAppointment(Appointment $appointment)
    {
        $appointment->update(['status' => 'refused']);

        Mail::to($appointment->patient->email)
            ->send(new AppointmentRefused($appointment));

        return back()->with('success', 'Rendez-vous refusé — Email envoyé');
    }

    public function profile()
    {
        $doctor = Auth::user()->doctor;
        return view('doctor.profile', compact('doctor'));
    }

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
