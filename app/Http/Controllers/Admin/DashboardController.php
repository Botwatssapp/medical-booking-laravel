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
        $chartSeries = $this->appointmentChartSeries();
        $weeklyDays = collect($chartSeries['daily']);

        $specialtyDemand = Appointment::query()
            ->join('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->join('specialities', 'specialities.id', '=', 'doctors.speciality_id')
            ->whereNull('doctors.deleted_at')
            ->select('specialities.name')
            ->selectRaw('COUNT(appointments.id) as total')
            ->groupBy('specialities.id', 'specialities.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $occupying = (int) ($appointmentStats->pending ?? 0)
            + (int) ($appointmentStats->accepted ?? 0)
            + (int) ($appointmentStats->completed ?? 0)
            + (int) ($appointmentStats->missed ?? 0);
        $freeSlots = Availability::query()->available()->count();
        $capacity = $occupying + $freeSlots;
        $thisMonthAppointments = Appointment::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $lastMonthAppointments = Appointment::query()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();

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
            'freeSlots' => $freeSlots,
            'fillRate' => $capacity > 0 ? (int) round(($occupying / $capacity) * 100) : null,
            'patientsThisWeek' => User::query()
                ->where('role', 'patient')
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
            'appointmentDelta' => $lastMonthAppointments > 0
                ? (int) round((($thisMonthAppointments - $lastMonthAppointments) / $lastMonthAppointments) * 100)
                : null,
            'weeklyDays' => $weeklyDays,
            'chartSeries' => $chartSeries,
            'specialtyDemand' => $specialtyDemand,
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

    /**
     * Volumes réels de rendez-vous : 7 jours, 7 semaines, 7 mois.
     *
     * @return array{daily: array<int, array{label: string, count: int}>, weekly: array<int, array{label: string, count: int}>, monthly: array<int, array{label: string, count: int}>}
     */
    private function appointmentChartSeries(): array
    {
        $from = now()->subMonths(6)->startOfMonth();
        $countsByDay = Appointment::query()
            ->where('appointment_date', '>=', $from)
            ->get(['appointment_date'])
            ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->toDateString())
            ->map->count();

        $sumRange = function ($start, $end) use ($countsByDay): int {
            $total = 0;
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $total += (int) ($countsByDay[$day->toDateString()] ?? 0);
            }

            return $total;
        };

        $daily = collect(range(6, 0))->map(function (int $offset) use ($countsByDay) {
            $day = now()->subDays($offset);

            return [
                'label' => $day->translatedFormat('D'),
                'count' => (int) ($countsByDay[$day->toDateString()] ?? 0),
            ];
        })->values()->all();

        $weekly = collect(range(6, 0))->map(function (int $offset) use ($sumRange) {
            $start = now()->subWeeks($offset)->startOfWeek();

            return [
                'label' => $start->translatedFormat('d M'),
                'count' => $sumRange($start, $start->copy()->endOfWeek()),
            ];
        })->values()->all();

        $monthly = collect(range(6, 0))->map(function (int $offset) use ($sumRange) {
            $month = now()->subMonths($offset)->startOfMonth();

            return [
                'label' => $month->translatedFormat('M'),
                'count' => $sumRange($month, $month->copy()->endOfMonth()),
            ];
        })->values()->all();

        return [
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
        ];
    }
}
