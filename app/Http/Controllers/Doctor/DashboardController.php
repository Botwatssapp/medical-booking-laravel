<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Tableau de bord médecin : statistiques et agenda du jour réels.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = auth()->user()->doctor;
        $selectedDate = $this->resolveDate($request);
        $agendaView = $this->resolveView($request);

        if (! $doctor) {
            return view('doctor.dashboard', $this->emptyDashboard(true, $selectedDate, $agendaView));
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

        $dayAppointments = $doctor->appointments()
            ->whereDate('appointment_date', $selectedDate)
            ->with($relations)
            ->orderBy('appointment_date')
            ->get();

        $todayAppointments = $dayAppointments->whereIn('status', [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_ACCEPTED,
        ])->values();

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

        $pendingQueue = $doctor->appointments()
            ->pending()
            ->with($relations)
            ->orderBy('appointment_date')
            ->limit(6)
            ->get();

        $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $selectedDate->copy()->startOfMonth();
        $monthEnd = $selectedDate->copy()->endOfMonth();

        $weekAppointments = $doctor->appointments()
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])
            ->with($relations)
            ->orderBy('appointment_date')
            ->get()
            ->groupBy(fn (Appointment $apt) => $apt->appointment_date->toDateString());

        $monthAppointments = $doctor->appointments()
            ->whereBetween('appointment_date', [$monthStart, $monthEnd])
            ->with($relations)
            ->orderBy('appointment_date')
            ->get()
            ->groupBy(fn (Appointment $apt) => $apt->appointment_date->toDateString());

        $freeSlots = $doctor->availabilities()->available()->count();

        $dayFreeSlots = $doctor->availabilities()
            ->whereDate('date', $selectedDate)
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        $monthCaseload = $doctor->appointments()
            ->whereIn('status', Appointment::OCCUPYING_STATUSES)
            ->whereBetween('appointment_date', [
                $selectedDate->copy()->startOfMonth(),
                $selectedDate->copy()->endOfMonth(),
            ])
            ->count();

        $weekDays = $this->weekAvailability($doctor, $selectedDate);
        $timeline = $this->buildTimeline($dayAppointments, $dayFreeSlots, $selectedDate);
        $activeNow = $this->activeConsultation($dayAppointments, $selectedDate);

        $todayDone = $dayAppointments->where('status', Appointment::STATUS_COMPLETED)->count();
        $todayActive = $activeNow ? 1 : 0;
        $todayNext = $todayAppointments->filter(function ($apt) use ($selectedDate) {
            if (! $selectedDate->isToday()) {
                return true;
            }
            $start = $this->appointmentStart($apt);

            return $start->greaterThan(now());
        })->count();

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
            'selectedDate' => $selectedDate,
            'pendingQueue' => $pendingQueue,
            'timeline' => $timeline,
            'activeNow' => $activeNow,
            'todayVisits' => $dayAppointments->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_ACCEPTED,
                Appointment::STATUS_COMPLETED,
            ])->count(),
            'todayDone' => $todayDone,
            'todayActive' => $todayActive,
            'todayNext' => $todayNext,
            'todayFreeSlots' => $dayFreeSlots->count(),
            'monthCaseload' => $monthCaseload,
            'weekDays' => $weekDays,
            'agendaView' => $agendaView,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekAppointments' => $weekAppointments,
            'monthAppointments' => $monthAppointments,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDashboard(bool $profileIncomplete, ?Carbon $selectedDate = null, string $agendaView = 'day'): array
    {
        $selectedDate ??= today();
        $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);

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
            'selectedDate' => $selectedDate,
            'pendingQueue' => collect(),
            'timeline' => collect(),
            'activeNow' => null,
            'todayVisits' => 0,
            'todayDone' => 0,
            'todayActive' => 0,
            'todayNext' => 0,
            'todayFreeSlots' => 0,
            'monthCaseload' => 0,
            'weekDays' => array_fill(1, 7, false),
            'agendaView' => $agendaView,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekAppointments' => collect(),
            'monthAppointments' => collect(),
        ];
    }

    private function resolveView(Request $request): string
    {
        $view = $request->query('view', 'day');

        return in_array($view, ['day', 'week', 'month'], true) ? $view : 'day';
    }

    private function resolveDate(Request $request): Carbon
    {
        $raw = $request->query('date');
        if (! is_string($raw) || $raw === '') {
            return today();
        }

        try {
            $date = Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return today();
        }

        if ($date->lt(now()->subYear()) || $date->gt(now()->addYear())) {
            return today();
        }

        return $date;
    }

    /**
     * @return array<int, bool>
     */
    private function weekAvailability($doctor, Carbon $selectedDate): array
    {
        $start = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $end = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);
        $days = array_fill(1, 7, false);

        $doctor->availabilities()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->each(function ($slot) use (&$days) {
                $iso = (int) $slot->date->isoWeekday();
                $days[$iso] = true;
            });

        return $days;
    }

    private function appointmentStart(Appointment $appointment): Carbon
    {
        if ($appointment->availability) {
            return $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->start_time);
        }

        return $appointment->appointment_date->copy();
    }

    private function appointmentEnd(Appointment $appointment): Carbon
    {
        if ($appointment->availability) {
            return $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->end_time);
        }

        return $appointment->appointment_date->copy()->addHour();
    }

    /**
     * @param  Collection<int, Appointment>  $dayAppointments
     */
    private function activeConsultation(Collection $dayAppointments, Carbon $selectedDate): ?Appointment
    {
        if (! $selectedDate->isToday()) {
            return null;
        }

        return $dayAppointments->first(function (Appointment $apt) {
            if ($apt->status !== Appointment::STATUS_ACCEPTED) {
                return false;
            }

            $start = $this->appointmentStart($apt);
            $end = $this->appointmentEnd($apt);

            return now()->betweenIncluded($start, $end);
        });
    }

    /**
     * @param  Collection<int, Appointment>  $dayAppointments
     * @param  Collection<int, mixed>  $dayFreeSlots
     * @return Collection<int, array<string, mixed>>
     */
    private function buildTimeline(Collection $dayAppointments, Collection $dayFreeSlots, Carbon $selectedDate): Collection
    {
        $items = collect();

        foreach ($dayAppointments as $apt) {
            $start = $this->appointmentStart($apt);
            $end = $this->appointmentEnd($apt);
            $items->push([
                'kind' => 'appointment',
                'appointment' => $apt,
                'start' => $start,
                'end' => $end,
                'minutes' => max(1, $start->diffInMinutes($end)),
                'active' => $selectedDate->isToday()
                    && $apt->status === Appointment::STATUS_ACCEPTED
                    && now()->betweenIncluded($start, $end),
            ]);
        }

        foreach ($dayFreeSlots as $slot) {
            $start = $selectedDate->copy()->setTimeFromTimeString($slot->start_time);
            $end = $selectedDate->copy()->setTimeFromTimeString($slot->end_time);
            $items->push([
                'kind' => 'slot',
                'availability' => $slot,
                'start' => $start,
                'end' => $end,
                'minutes' => max(1, $start->diffInMinutes($end)),
                'active' => false,
            ]);
        }

        return $items->sortBy(fn (array $item) => $item['start']->timestamp)->values();
    }
}
