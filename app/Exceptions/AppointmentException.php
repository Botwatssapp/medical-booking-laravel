<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception métier levée par le moteur de rendez-vous
 * (transition interdite, créneau indisponible, etc.).
 */
class AppointmentException extends RuntimeException {}
