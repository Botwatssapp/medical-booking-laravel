<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\View\View;

/**
 * Tableau de bord médecin : statistiques et prochains rendez-vous réels.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            return view('doctor.dashboard', $this->emptyDashboard(true));
        }

        $stats = $doctor->appointments()
            ->selectRaw('
                COUNT(*) as total,
                SUM(status = \''.Appointment::STATUS_PENDING.'\') as pending,
                SUM(status = \''.Appointment::STATUS_ACCEPTED.'\') as accepted,
                SUM(status = \''.Appointment::STATUS_REJECTED.'\') as rejected,
                SUM(status = \''.Appointment::STATUS_COMPLETED.'\') as completed,
                SUM(status = \''.Appointment::STATUS_MISSED.'\') as missed,
                SUM(status = \''.Appointment::STATUS_CANCELLED.'\') as cancelled
            ')
            ->first();

        $relations = ['patient', 'availability'];

        $todayAppointments = $doctor->appointments()
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_ACCEPTED,
            ])
            ->whereDate('appointment_date', today())
            ->with($relations)
            ->orderBy('appointment_date')
            ->get();

        $upcomingAppointments = $doctor->appointments()
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_ACCEPTED,
            ])
            ->upcoming()
            ->with($relations)
            ->orderBy('appointment_date')
            ->limit(5)
            ->get();

        $freeSlots = $doctor->availabilities()->available()->count();

        return view('doctor.dashboard', [
            'profileIncomplete' => false,
            'totalAppointments' => $stats->total ?? 0,
            'pendingAppointments' => $stats->pending ?? 0,
            'acceptedAppointments' => $stats->accepted ?? 0,
            'rejectedAppointments' => $stats->rejected ?? 0,
            'completedAppointments' => $stats->completed ?? 0,
            'missedAppointments' => $stats->missed ?? 0,
            'cancelledAppointments' => $stats->cancelled ?? 0,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'nextAppointment' => $upcomingAppointments->first(),
            'freeSlots' => $freeSlots,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDashboard(bool $profileIncomplete): array
    {
        return [
            'profileIncomplete' => $profileIncomplete,
            'totalAppointments' => 0,
            'pendingAppointments' => 0,
            'acceptedAppointments' => 0,
            'rejectedAppointments' => 0,
            'completedAppointments' => 0,
            'missedAppointments' => 0,
            'cancelledAppointments' => 0,
            'todayAppointments' => collect(),
            'upcomingAppointments' => collect(),
            'nextAppointment' => null,
            'freeSlots' => 0,
        ];
    }
}
