<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Tableau de bord patient : statistiques et prochains rendez-vous réels.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $patientId = Auth::id();

        $base = Appointment::query()->where('patient_id', $patientId);

        $totalAppointments = (clone $base)->count();
        $pendingAppointments = (clone $base)->pending()->count();
        $confirmedAppointments = (clone $base)->accepted()->count();
        $completedAppointments = (clone $base)->completed()->count();
        $cancelledAppointments = (clone $base)->whereIn('status', [
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_REJECTED,
        ])->count();

        $relations = ['doctor.user', 'doctor.speciality', 'availability'];

        $upcomingAppointments = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_ACCEPTED,
            ])
            ->upcoming()
            ->with($relations)
            ->orderBy('appointment_date')
            ->limit(5)
            ->get();

        $pendingList = Appointment::query()
            ->where('patient_id', $patientId)
            ->pending()
            ->with($relations)
            ->orderBy('appointment_date')
            ->limit(3)
            ->get();

        $pastAppointments = Appointment::query()
            ->where('patient_id', $patientId)
            ->completed()
            ->past()
            ->with($relations)
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();

        return view('patient.dashboard', [
            'totalAppointments' => $totalAppointments,
            'confirmedAppointments' => $confirmedAppointments,
            'pendingAppointments' => $pendingAppointments,
            'completedAppointments' => $completedAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'pendingList' => $pendingList,
            'pastAppointments' => $pastAppointments,
        ]);
    }
}
