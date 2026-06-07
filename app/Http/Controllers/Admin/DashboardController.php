<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPatients      = User::patients()->count();
        $totalDoctors       = Doctor::count();
        $totalAppointments  = Appointment::count();
        $todayAppointments  = Appointment::whereDate('appointment_date', today())->count();

        $acceptedAppointments  = Appointment::accepted()->count();
        $pendingAppointments   = Appointment::pending()->count();
        $cancelledAppointments = Appointment::cancelled()->count();
        $missedAppointments    = Appointment::missed()->count();

        $pendingDoctors = User::doctors()->doesntHave('doctor')->orderBy('created_at', 'desc')->get();

        $appointmentsBySpecialty = Speciality::withCount([
            'doctors',
            'doctors as appointments_count' => function ($query) {
                $query->join('appointments', 'appointments.doctor_id', '=', 'doctors.id')
                      ->whereNull('appointments.deleted_at');
            },
        ])->get()->map(fn (Speciality $s) => [
            'name'  => $s->name,
            'count' => $s->appointments_count ?? 0,
        ]);

        $recentAppointments = Appointment::with(['patient', 'doctor.user', 'doctor.speciality'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'totalAppointments',
            'todayAppointments',
            'acceptedAppointments',
            'pendingAppointments',
            'cancelledAppointments',
            'missedAppointments',
            'pendingDoctors',
            'appointmentsBySpecialty',
            'recentAppointments',
        ));
    }
}
