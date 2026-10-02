<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception métier du flux d'onboarding médecin
 * (profil déjà existant, rôle invalide, spécialité manquante, etc.).
 */
class DoctorException extends RuntimeException {}
