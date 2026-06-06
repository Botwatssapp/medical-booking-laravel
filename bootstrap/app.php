<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap de l'application Laravel 12
|--------------------------------------------------------------------------
|
| Configuration du kernel HTTP :
| - Routage : web.php (+ auth.php inclus) et console.php
| - Middleware aliases pour le contrôle des rôles
| - Endpoint de santé : /up
|
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases pour les middlewares de contrôle de rôle
        $middleware->alias([
            'role'    => \App\Http\Middleware\RoleMiddleware::class,
            'admin'   => \App\Http\Middleware\AdminMiddleware::class,
            'doctor'  => \App\Http\Middleware\DoctorMiddleware::class,
            'patient' => \App\Http\Middleware\PatientMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
