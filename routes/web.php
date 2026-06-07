<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointment;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\DoctorController as AdminDoctor;
use App\Http\Controllers\Admin\SpecialtyController as AdminSpecialty;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointment;
use App\Http\Controllers\Doctor\AvailabilityController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboard;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfile;
use App\Http\Controllers\Patient\AppointmentController as PatientAppointment;
use App\Http\Controllers\Patient\DashboardController as PatientDashboard;
use App\Http\Controllers\Patient\DoctorController as PatientDoctor;
use App\Http\Controllers\Patient\NotificationController as PatientNotification;
use App\Http\Controllers\Patient\ProfileController as PatientProfile;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Les routes sont organisées par rôle : admin, doctor, patient.
| Chaque groupe est protégé par le middleware d'authentification
| et le middleware de rôle correspondant.
|
*/

// Page d'accueil : redirige vers le bon tableau de bord si connecté, sinon login
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'doctor' => redirect()->route('doctor.dashboard'),
            default  => redirect()->route('patient.dashboard'),
        };
    }
    return view('auth.login');
})->name('home');

// =========================================================================
// Routes Admin
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // Tableau de bord administrateur
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');

    // Gestion des utilisateurs (sans show)
    Route::resource('users', AdminUser::class)
        ->names('admin.users')
        ->except('show');

    // Gestion des médecins (sans show)
    Route::resource('doctors', AdminDoctor::class)
        ->names('admin.doctors')
        ->except('show');

    // Gestion des spécialités (sans show)
    Route::resource('specialties', AdminSpecialty::class)
        ->names('admin.specialties')
        ->except('show');

    // Gestion des rendez-vous (lecture + contrôle complet)
    Route::get('appointments', [AdminAppointment::class, 'index'])->name('admin.appointments.index');
    Route::get('appointments/{appointment}', [AdminAppointment::class, 'show'])->name('admin.appointments.show');
    Route::post('appointments/{appointment}/cancel', [AdminAppointment::class, 'cancel'])->name('admin.appointments.cancel');
    Route::patch('appointments/{appointment}/status', [AdminAppointment::class, 'updateStatus'])->name('admin.appointments.updateStatus');

    // Profil admin
    Route::get('profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profile.update');
    Route::delete('profile/image', [\App\Http\Controllers\Admin\ProfileController::class, 'removeImage'])->name('admin.profile.removeImage');
});

// =========================================================================
// Routes Patient
// =========================================================================
Route::middleware(['auth', 'patient'])->prefix('patient')->group(function () {

    // Tableau de bord patient (utilise le contrôleur pour charger les données)
    Route::get('/dashboard', [PatientDashboard::class, 'index'])->name('patient.dashboard');

    // Annuaire des médecins
    Route::get('doctors', [PatientDoctor::class, 'index'])->name('patient.doctors.index');
    Route::get('doctors/{doctor}', [PatientDoctor::class, 'show'])->name('patient.doctors.show');

    // Gestion des rendez-vous du patient
    Route::resource('appointments', PatientAppointment::class)
        ->names('patient.appointments');

    // Profil patient
    Route::get('profile/edit', [PatientProfile::class, 'edit'])->name('patient.profile.edit');
    Route::patch('profile', [PatientProfile::class, 'update'])->name('patient.profile.update');
    Route::delete('profile/image', [PatientProfile::class, 'removeImage'])->name('patient.profile.removeImage');

    // Notifications internes
    Route::get('notifications', [PatientNotification::class, 'index'])->name('patient.notifications.index');
    Route::patch('notifications/read-all', [PatientNotification::class, 'markAllRead'])->name('patient.notifications.readAll');
    Route::patch('notifications/{id}/read', [PatientNotification::class, 'markRead'])->name('patient.notifications.read');
});

// =========================================================================
// Routes Doctor
// =========================================================================
Route::middleware(['auth', 'doctor'])->prefix('doctor')->group(function () {

    // Tableau de bord médecin
    Route::get('/dashboard', [DoctorDashboard::class, 'index'])->name('doctor.dashboard');

    // Gestion des disponibilités
    Route::resource('availabilities', AvailabilityController::class)
        ->names('doctor.availabilities');

    // Gestion des rendez-vous du médecin
    Route::get('appointments', [DoctorAppointment::class, 'index'])->name('doctor.appointments.index');
    Route::get('appointments/{appointment}', [DoctorAppointment::class, 'show'])->name('doctor.appointments.show');
    Route::patch('appointments/{appointment}', [DoctorAppointment::class, 'update'])->name('doctor.appointments.update');
    Route::delete('appointments/{appointment}', [DoctorAppointment::class, 'destroy'])->name('doctor.appointments.destroy');
    // Annulation + report automatique sur le premier créneau disponible
    Route::post('appointments/{appointment}/reschedule', [DoctorAppointment::class, 'reschedule'])->name('doctor.appointments.reschedule');

    // Profil médecin
    Route::get('profile/edit', [DoctorProfile::class, 'edit'])->name('doctor.profile.edit');
    Route::patch('profile', [DoctorProfile::class, 'update'])->name('doctor.profile.update');
    Route::delete('profile/image', [DoctorProfile::class, 'removeImage'])->name('doctor.profile.removeImage');
});

// =========================================================================
// Routes d'authentification (Breeze)
// =========================================================================
require __DIR__.'/auth.php';
