<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', fn() => view('welcome'));

// Auth
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// Patient
Route::middleware(['auth', 'role:patient'])->prefix('patient')->group(function () {
    Route::get('/dashboard',              [PatientController::class, 'dashboard'])->name('patient.dashboard');
    Route::get('/doctors',                [PatientController::class, 'doctors'])->name('patient.doctors');
    Route::get('/doctors/{doctor}/book',  [PatientController::class, 'bookAppointment'])->name('patient.book');
    Route::post('/appointments',          [PatientController::class, 'storeAppointment'])->name('patient.appointments.store');
    Route::get('/appointments',           [AppointmentController::class, 'index'])->name('patient.appointments');
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('patient.appointments.update');
    Route::delete('/appointments/{appointment}',[AppointmentController::class, 'destroy'])->name('patient.appointments.destroy');
});

// Doctor
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->group(function () {
    Route::get('/dashboard',                          [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    Route::get('/profile',                            [DoctorController::class, 'profile'])->name('doctor.profile');
    Route::put('/profile',                            [DoctorController::class, 'updateProfile'])->name('doctor.profile.update');
    Route::patch('/appointments/{appointment}/confirm',[DoctorController::class, 'confirmAppointment'])->name('doctor.confirm');
    Route::patch('/appointments/{appointment}/refuse', [DoctorController::class, 'refuseAppointment'])->name('doctor.refuse');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard',                [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users',                    [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{user}',          [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/doctors',                  [AdminController::class, 'doctors'])->name('admin.doctors');
    Route::post('/doctors',                 [AdminController::class, 'storeDoctor'])->name('admin.doctors.store');
    Route::delete('/doctors/{doctor}',      [AdminController::class, 'deleteDoctor'])->name('admin.doctors.delete');
    Route::get('/specialities',             [AdminController::class, 'specialities'])->name('admin.specialities');
    Route::post('/specialities',            [AdminController::class, 'storeSpeciality'])->name('admin.specialities.store');
    Route::delete('/specialities/{speciality}',[AdminController::class, 'deleteSpeciality'])->name('admin.specialities.delete');
    Route::get('/appointments',             [AdminController::class, 'appointments'])->name('admin.appointments');
});
