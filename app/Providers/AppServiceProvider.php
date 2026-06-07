<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Policies\AppointmentPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Fournisseur de services principal de l'application.
 *
 * Ce ServiceProvider bootstrap :
 * - Le moteur de pagination (Tailwind CSS)
 * - Les Policies d'autorisation Eloquent
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap des services de l'application.
     *
     * - Configure la pagination avec Tailwind CSS.
     * - Enregistre les Policies via Gate::policy() (méthode recommandée
     *   dans Laravel 12 sans AuthServiceProvider dédié).
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Enregistrement des Policies d'autorisation
        Gate::policy(Appointment::class, AppointmentPolicy::class);

        // Redirection du middleware 'guest' vers le bon tableau de bord selon le rôle.
        // Sans cette configuration, Laravel cherche route('dashboard') puis route('home').
        // Or route('home') = '/' qui affiche la page de login → boucle infinie.
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            return match (auth()->user()?->role) {
                'admin'  => route('admin.dashboard'),
                'doctor' => route('doctor.dashboard'),
                default  => route('patient.dashboard'),
            };
        });
    }
}
