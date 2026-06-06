<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : création de la table appointments.
 *
 * Cette table est le cœur du système : elle lie un patient à un médecin
 * sur un créneau de disponibilité à une date/heure précise.
 *
 * Colonnes :
 * - id               : identifiant unique
 * - patient_id       : clé étrangère vers users (le patient)
 * - doctor_id        : clé étrangère vers doctors
 * - availability_id  : clé étrangère nullable vers availabilities
 *                      (SET NULL si le créneau est supprimé)
 * - appointment_date : date et heure exacte du rendez-vous
 * - status           : statut du rendez-vous (enum)
 * - notes            : notes additionnelles du patient
 * - deleted_at       : soft-delete pour préserver l'historique
 *
 * Statuts possibles :
 * - pending   : en attente de validation par le médecin
 * - accepted  : accepté par le médecin
 * - rejected  : refusé par le médecin
 * - cancelled : annulé par le patient ou le médecin
 * - completed : rendez-vous effectué
 * - missed    : patient absent au rendez-vous
 *
 * Index pour optimiser les requêtes fréquentes :
 * - (patient_id, status) : liste des rdv d'un patient par statut
 * - (doctor_id, status)  : liste des rdv d'un médecin par statut
 * - appointment_date     : tri et filtrage par date
 */
return new class extends Migration
{
    /**
     * Crée la table appointments.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Patient ayant réservé le rendez-vous
            $table->foreignId('patient_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Médecin concerné par le rendez-vous
            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('cascade');

            // Créneau de disponibilité (nullable : SET NULL si supprimé)
            $table->foreignId('availability_id')
                  ->nullable()
                  ->constrained('availabilities')
                  ->onDelete('set null');

            // Date et heure exacte du rendez-vous
            $table->dateTime('appointment_date');

            // Statut du rendez-vous avec sa valeur par défaut
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'cancelled',
                'completed',
                'missed',
            ])->default('pending');

            // Notes optionnelles du patient ou du médecin
            $table->text('notes')->nullable();

            // Soft-delete pour audit et historique
            $table->softDeletes();
            $table->timestamps();

            // Index pour les requêtes de liste par patient/médecin et statut
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index('appointment_date');
        });
    }

    /**
     * Supprime la table appointments.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
