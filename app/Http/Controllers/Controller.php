<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Contrôleur de base de l'application.
 *
 * Fournit le trait AuthorizesRequests à tous les contrôleurs enfants
 * pour l'utilisation de $this->authorize() avec les Policies.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
