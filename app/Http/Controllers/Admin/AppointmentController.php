<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AppointmentException;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Speciality;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $query = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.speciality',
            'availability',
        ]);

        $allowedStatuses = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_ACCEPTED,
            Appointment::STATUS_REJECTED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_MISSED,
        ];

        if ($request->filled('status') && in_array($request->status, $allowedStatuses, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('doctor.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('speciality_id')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('speciality_id', $request->integer('speciality_id'));
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }

        if ($request->filled('date')) {
            try {
                $query->whereDate('appointment_date', Carbon::parse((string) $request->query('date'))->toDateString());
            } catch (\Throwable) {
                // Date invalide : le filtre est ignoré.
            }
        }

        $allowedSorts = ['appointment_date', 'status'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'appointment_date';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $appointments = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        $counts = Appointment::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(status = \''.Appointment::STATUS_PENDING.'\') as pending,
                SUM(status = \''.Appointment::STATUS_ACCEPTED.'\') as accepted,
                SUM(status = \''.Appointment::STATUS_COMPLETED.'\') as completed,
                SUM(status = \''.Appointment::STATUS_CANCELLED.'\') as cancelled
            ')
            ->first();

        $stats = [
            'total' => (int) ($counts->total ?? 0),
            'pending' => (int) ($counts->pending ?? 0),
            'accepted' => (int) ($counts->accepted ?? 0),
            'completed' => (int) ($counts->completed ?? 0),
            'cancelled' => (int) ($counts->cancelled ?? 0),
        ];

        $selectedId = $request->integer('selected') ?: null;
        $selected = $selectedId
            ? ($appointments->firstWhere('id', $selectedId)
                ?? Appointment::query()->with(['patient', 'doctor.user', 'doctor.speciality', 'availability'])->find($selectedId))
            : $appointments->first();

        $patientAppointmentCount = $selected
            ? Appointment::query()->where('patient_id', $selected->patient_id)->count()
            : 0;

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'stats' => $stats,
            'specialties' => Speciality::query()->orderBy('name')->get(),
            'selected' => $selected,
            'patientAppointmentCount' => $patientAppointmentCount,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load([
            'patient',
            'doctor.user',
            'doctor.speciality',
            'availability',
        ]);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('cancel', $appointment);

        try {
            $this->appointments->cancel($appointment);
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Rendez-vous annulé avec succès.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('applyAdminStatus', $appointment);

        $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected,cancelled,completed,missed'],
        ]);

        try {
            $this->appointments->applyAdminStatus($appointment, $request->string('status')->toString());
        } catch (AppointmentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Statut mis à jour avec succès.');
    }
}
