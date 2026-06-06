<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : création de la table availabilities.
 *
 * Cette table définit les créneaux horaires disponibles pour chaque médecin.
 * Un créneau est défini par une date, une heure de début et une heure de fin.
 *
 * Colonnes :
 * - id           : identifiant unique
 * - doctor_id    : clé étrangère vers doctors (cascade delete)
 * - date         : date du créneau (format YYYY-MM-DD)
 * - start_time   : heure de début (format HH:MM)
 * - end_time     : heure de fin (format HH:MM, doit être > start_time)
 * - is_available : true si le créneau peut encore être réservé
 *
 * Contraintes :
 * - Unicité sur (doctor_id, date, start_time, end_time) pour éviter les doublons
 * - Index composé (doctor_id, date, is_available) pour les requêtes de recherche
 *
 * Note : pas de soft-delete car les créneaux passés peuvent simplement être filtrés
 */
return new class extends Migration
{
    /**
     * Crée la table availabilities.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();

            // Référence au médecin propriétaire du créneau
            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('cascade');

            // Date du créneau de disponibilité
            $table->date('date');

            // Heure de début du créneau (HH:MM:SS)
            $table->time('start_time');

            // Heure de fin du créneau (doit être strictement > start_time)
            $table->time('end_time');

            // Indique si le créneau peut encore être réservé
            // Passage à false lors de la confirmation d'un rendez-vous
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            // Index composé pour optimiser les requêtes de disponibilité
            // Usage typique : WHERE doctor_id = ? AND is_available = true AND date >= ?
            $table->index(['doctor_id', 'date', 'is_available']);

            // Contrainte d'unicité pour éviter les créneaux dupliqués
            $table->unique(['doctor_id', 'date', 'start_time', 'end_time']);
        });
    }

    /**
     * Supprime la table availabilities.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
