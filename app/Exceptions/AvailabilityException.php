<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception métier levée par le moteur de disponibilités
 * (doublon, chevauchement, suppression d'un créneau occupé, etc.).
 */
class AvailabilityException extends RuntimeException {}
