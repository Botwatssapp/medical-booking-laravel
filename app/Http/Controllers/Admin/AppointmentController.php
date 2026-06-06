<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\View\View;

/**
 * Contrôleur de consultation des rendez-vous côté administrateur.
 *
 * L'administrateur dispose d'une vue en lecture seule sur tous les rendez-vous
 * du système, avec les informations patient et médecin associées.
 */
class AppointmentController extends Controller
{
    /**
     * Affiche la liste paginée de tous les rendez-vous du système.
     *
     * Utilise l'eager loading pour patient, doctor et speciality
     * afin d'éviter les requêtes N+1.
     *
     * @return View
     */
    public function index(): View
    {
        $appointments = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.speciality',
        ])
        ->orderBy('appointment_date', 'desc')
        ->paginate(15);

        return view('admin.appointments.index', compact('appointments'));
    }

    /**
     * Affiche le détail d'un rendez-vous spécifique.
     *
     * Charge toutes les relations nécessaires à l'affichage complet.
     *
     * @param  Appointment $appointment
     * @return View
     */
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
}
