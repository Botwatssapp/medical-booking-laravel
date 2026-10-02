<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\View\View;

/**
 * Tableau de bord administrateur : indicateurs réels uniquement.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $userStats = User::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(role = 'patient') as patients,
                SUM(role = 'doctor') as doctor_accounts,
                SUM(role = 'admin') as admins
            ")
            ->first();

        $appointmentStats = Appointment::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(status = \''.Appointment::STATUS_PENDING.'\') as pending,
                SUM(status = \''.Appointment::STATUS_ACCEPTED.'\') as accepted,
                SUM(status = \''.Appointment::STATUS_REJECTED.'\') as rejected,
                SUM(status = \''.Appointment::STATUS_COMPLETED.'\') as completed,
                SUM(status = \''.Appointment::STATUS_CANCELLED.'\') as cancelled,
                SUM(status = \''.Appointment::STATUS_MISSED.'\') as missed
            ')
            ->first();

        $relations = ['patient', 'doctor.user', 'doctor.speciality', 'availability'];

        return view('admin.dashboard', [
            'totalUsers' => (int) ($userStats->total ?? 0),
            'totalPatients' => (int) ($userStats->patients ?? 0),
            'doctorAccounts' => (int) ($userStats->doctor_accounts ?? 0),
            'totalAdmins' => (int) ($userStats->admins ?? 0),
            'totalDoctors' => Doctor::count(),
            'pendingDoctorCount' => User::doctors()->doesntHave('doctor')->count(),
            'totalAppointments' => (int) ($appointmentStats->total ?? 0),
            'todayAppointments' => Appointment::query()->whereDate('appointment_date', today())->count(),
            'pendingAppointments' => (int) ($appointmentStats->pending ?? 0),
            'acceptedAppointments' => (int) ($appointmentStats->accepted ?? 0),
            'rejectedAppointments' => (int) ($appointmentStats->rejected ?? 0),
            'completedAppointments' => (int) ($appointmentStats->completed ?? 0),
            'cancelledAppointments' => (int) ($appointmentStats->cancelled ?? 0),
            'missedAppointments' => (int) ($appointmentStats->missed ?? 0),
            'freeSlots' => Availability::query()->available()->count(),
            'pendingDoctors' => User::doctors()
                ->doesntHave('doctor')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'upcomingAppointments' => Appointment::query()
                ->whereIn('status', [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_ACCEPTED,
                ])
                ->upcoming()
                ->with($relations)
                ->orderBy('appointment_date')
                ->limit(5)
                ->get(),
            'recentAppointments' => Appointment::query()
                ->with($relations)
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
