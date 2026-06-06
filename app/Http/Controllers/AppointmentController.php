<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\AppointmentCancelled;
use Illuminate\Support\Facades\Mail;


class AppointmentController extends Controller
{
    // Liste des RDV du patient
    public function index()
    {
        $appointments = Appointment::with('doctor.user', 'doctor.speciality')
            ->where('patient_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('patient.appointments', compact('appointments'));
    }

    // Modifier RDV
    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'date'      => 'required|date|after:today',
            'time_slot' => 'required|string',
        ]);

        $appointment->update([
            'date'      => $request->date,
            'time_slot' => $request->time_slot,
            'status'    => 'pending',
        ]);

        return back()->with('success', 'Rendez-vous modifié avec succès');
    }

    // Annuler RDV
    public function destroy(Appointment $appointment)
{
    if ($appointment->patient_id !== Auth::id()) {
        abort(403);
    }

    $appointment->update(['status' => 'cancelled']);

    // ✅ صيفط email للمريض
    Mail::to($appointment->patient->email)
        ->send(new AppointmentCancelled($appointment));

    return back()->with('success', 'Rendez-vous annulé — Email envoyé');
}
}
