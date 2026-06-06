<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    // Dashboard Patient
    public function dashboard()
    {
        $appointments = Appointment::with('doctor.user', 'doctor.speciality')
            ->where('patient_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('patient.dashboard', compact('appointments'));
    }

    // Liste des médecins
    public function doctors(Request $request)
    {
        $specialities = Speciality::all();

        $doctors = Doctor::with('user', 'speciality')
            ->when($request->speciality, function ($q) use ($request) {
                $q->where('speciality_id', $request->speciality);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            })
            ->where('is_available', true)
            ->get();

        return view('patient.doctors', compact('doctors', 'specialities'));
    }

    // صفحة حجز RDV
    public function bookAppointment(Doctor $doctor)
    {
        return view('patient.book', compact('doctor'));
    }

    // تأكيد حجز RDV
    public function storeAppointment(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date'      => 'required|date|after:today',
            'time_slot' => 'required|string',
            'reason'    => 'nullable|string|max:300',
        ]);

        Appointment::create([
            'patient_id' => Auth::id(),
            'doctor_id'  => $request->doctor_id,
            'date'       => $request->date,
            'time_slot'  => $request->time_slot,
            'reason'     => $request->reason,
            'status'     => 'pending',
        ]);

        return redirect('/patient/dashboard')
            ->with('success', 'Rendez-vous réservé avec succès');
    }

    // إلغاء RDV
    public function cancelAppointment(Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::id()) {
            abort(403);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Rendez-vous annulé');
    }
}
