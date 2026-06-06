<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord administrateur.
 *
 * Affiche les statistiques globales du système :
 * nombre de patients, médecins, rendez-vous, et répartition par spécialité.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur avec les statistiques globales.
     *
     * Optimisations appliquées :
     * - withCount() pour éviter les requêtes N+1 sur les spécialités
     * - Agrégations SQL directes pour les compteurs de statut
     *
     * @return View
     */
    public function index(): View
    {
        $totalPatients      = User::patients()->count();
        $totalDoctors       = Doctor::count();
        $totalAppointments  = Appointment::count();
        $todayAppointments  = Appointment::whereDate('appointment_date', today())->count();
        $acceptedAppointments  = Appointment::accepted()->count();
        $cancelledAppointments = Appointment::cancelled()->count();
        $missedAppointments    = Appointment::missed()->count();

        // Correction BUG : ajout de `use ($specialty)` dans la closure.
        // Optimisation : une seule requête SQL via withCount + sous-requête agrégée
        // au lieu d'une requête par spécialité (suppression N+1).
        $appointmentsBySpecialty = Speciality::withCount([
            'doctors',
            'doctors as appointments_count' => function ($query) {
                $query->join('appointments', 'appointments.doctor_id', '=', 'doctors.id')
                      ->whereNull('appointments.deleted_at');
            },
        ])->get()->map(fn (Speciality $specialty) => [
            'name'  => $specialty->name,
            'count' => $specialty->appointments_count ?? 0,
        ]);

        return view('admin.dashboard', [
            'totalPatients'          => $totalPatients,
            'totalDoctors'           => $totalDoctors,
            'totalAppointments'      => $totalAppointments,
            'todayAppointments'      => $todayAppointments,
            'appointmentsBySpecialty' => $appointmentsBySpecialty,
            'acceptedAppointments'   => $acceptedAppointments,
            'cancelledAppointments'  => $cancelledAppointments,
            'missedAppointments'     => $missedAppointments,
        ]);
    }
}
