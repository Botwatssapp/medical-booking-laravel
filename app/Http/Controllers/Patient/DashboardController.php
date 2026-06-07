<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord patient.
 *
 * Affiche les statistiques personnelles du patient et ses prochains
 * rendez-vous via des requêtes SQL optimisées.
 *
 * Correction : remplacement du chargement global en mémoire par des
 * requêtes SQL ciblées (suppression du problème de filtrage PHP en mémoire).
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du patient avec ses statistiques.
     *
     * Toutes les statistiques sont calculées directement en SQL
     * pour éviter de charger l'intégralité des rendez-vous en mémoire.
     *
     * @return View
     */
    public function index(): View
    {
        $patientId = Auth::id();

        // Compteurs via SQL — évite le chargement de tous les enregistrements
        $totalAppointments     = Appointment::where('patient_id', $patientId)->count();
        $confirmedAppointments = Appointment::where('patient_id', $patientId)->accepted()->count();
        $pendingAppointments   = Appointment::where('patient_id', $patientId)->pending()->count();
        $cancelledAppointments = Appointment::where('patient_id', $patientId)->cancelled()->count();

        // Prochains rendez-vous acceptés (futurs), limités à 5 — requête SQL
        $upcomingAppointments = Appointment::where('patient_id', $patientId)
            ->accepted()
            ->upcoming()
            ->with(['doctor.user', 'doctor.speciality', 'availability'])
            ->orderBy('appointment_date')
            ->limit(5)
            ->get();

        // Rendez-vous passés et terminés, limités à 5 — requête SQL
        $pastAppointments = Appointment::where('patient_id', $patientId)
            ->completed()
            ->past()
            ->with(['doctor.user', 'doctor.speciality', 'availability'])
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();

        return view('patient.dashboard', [
            'totalAppointments'     => $totalAppointments,
            'confirmedAppointments' => $confirmedAppointments,
            'pendingAppointments'   => $pendingAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'upcomingAppointments'  => $upcomingAppointments,
            'pastAppointments'      => $pastAppointments,
        ]);
    }
}
