<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord médecin.
 *
 * Affiche les statistiques personnelles du médecin et ses prochains
 * rendez-vous. Les compteurs sont calculés via des agrégations SQL
 * optimisées.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du médecin connecté.
     *
     * Optimisation : les 4 compteurs sont calculés en une seule requête
     * via `selectRaw` au lieu de 4 requêtes séparées.
     *
     * @return View
     */
    public function index(): View
    {
        $doctor = auth()->user()->doctor;

        // Médecin inscrit mais profil pas encore créé par l'admin
        if (!$doctor) {
            return view('doctor.dashboard', [
                'profileIncomplete'    => true,
                'totalAppointments'    => 0,
                'pendingAppointments'  => 0,
                'acceptedAppointments' => 0,
                'rejectedAppointments' => 0,
                'upcomingAppointments' => collect(),
            ]);
        }

        // Agrégation des statuts en une seule requête SQL
        $stats = $doctor->appointments()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending')   as pending,
                SUM(status = 'accepted')  as accepted,
                SUM(status = 'rejected')  as rejected
            ")
            ->first();

        // Prochains rendez-vous futurs avec les données du patient et du créneau
        $upcomingAppointments = $doctor->appointments()
            ->with(['patient', 'availability'])
            ->upcoming()
            ->orderBy('appointment_date')
            ->limit(5)
            ->get();

        return view('doctor.dashboard', [
            'profileIncomplete'    => false,
            'totalAppointments'    => $stats->total    ?? 0,
            'pendingAppointments'  => $stats->pending  ?? 0,
            'acceptedAppointments' => $stats->accepted ?? 0,
            'rejectedAppointments' => $stats->rejected ?? 0,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }
}
