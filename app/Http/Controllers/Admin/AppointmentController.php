<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.speciality',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', "%$search%"))
                  ->orWhereHas('doctor.user', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $allowedSorts = ['appointment_date', 'status'];
        $sort      = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'appointment_date';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $appointments = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        $stats = [
            'total'     => Appointment::count(),
            'pending'   => Appointment::pending()->count(),
            'accepted'  => Appointment::accepted()->count(),
            'cancelled' => Appointment::cancelled()->count(),
        ];

        return view('admin.appointments.index', compact('appointments', 'stats'));
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
        if (in_array($appointment->status, ['cancelled', 'completed', 'missed'])) {
            return back()->with('error', 'Ce rendez-vous ne peut pas être annulé.');
        }

        DB::transaction(function () use ($appointment) {
            if ($appointment->availability) {
                $appointment->availability->update(['is_available' => true]);
            }
            $appointment->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Rendez-vous annulé avec succès.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected,cancelled,completed,missed'],
        ]);

        $newStatus = $request->status;

        DB::transaction(function () use ($appointment, $newStatus) {
            $oldStatus = $appointment->status;

            // If we're cancelling or rejecting, free the slot
            if (in_array($newStatus, ['cancelled', 'rejected']) && !in_array($oldStatus, ['cancelled', 'rejected'])) {
                if ($appointment->availability) {
                    $appointment->availability->update(['is_available' => true]);
                }
            }

            // If we're reactivating from cancelled/rejected, re-lock the slot
            if (!in_array($newStatus, ['cancelled', 'rejected']) && in_array($oldStatus, ['cancelled', 'rejected'])) {
                if ($appointment->availability) {
                    $appointment->availability->update(['is_available' => false]);
                }
            }

            $appointment->update(['status' => $newStatus]);
        });

        return back()->with('success', 'Statut mis à jour avec succès.');
    }
}
