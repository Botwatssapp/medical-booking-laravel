<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : création de la table specialities.
 *
 * Cette table stocke le référentiel des spécialités médicales.
 * Elle est référencée par la table doctors via une clé étrangère.
 *
 * Colonnes :
 * - id          : identifiant unique auto-incrémenté
 * - name        : nom de la spécialité (unique pour éviter les doublons)
 * - description : description optionnelle de la spécialité
 * - deleted_at  : soft-delete (la suppression est logique, pas physique)
 * - timestamps  : created_at et updated_at
 */
return new class extends Migration
{
    /**
     * Crée la table specialities.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('specialities', function (Blueprint $table) {
            $table->id();

            // Nom unique pour éviter les doublons de spécialité
            $table->string('name')->unique();

            // Description optionnelle de la spécialité médicale
            $table->text('description')->nullable();

            // Soft-delete : la suppression est logique pour préserver l'historique
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Supprime la table specialities.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('specialities');
    }
};
