<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : création de la table doctors.
 *
 * Cette table étend le profil d'un utilisateur de rôle 'doctor'.
 * Relation 1:1 avec users et N:1 avec specialities.
 *
 * Colonnes :
 * - id             : identifiant unique
 * - user_id        : clé étrangère vers users (cascade delete)
 * - speciality_id  : clé étrangère vers specialities (cascade delete)
 * - phone          : téléphone professionnel (optionnel)
 * - address        : adresse du cabinet (optionnel)
 * - bio            : biographie / présentation (optionnel)
 * - photo          : chemin de la photo de profil dans le storage
 * - deleted_at     : soft-delete
 *
 * Contraintes :
 * - La suppression d'un utilisateur supprime son profil médecin (cascade)
 * - La suppression d'une spécialité supprime ses médecins (cascade)
 *
 * Index :
 * - speciality_id et user_id : ajoutés pour optimiser les jointures
 *   (foreignId() crée déjà les FK, les index ici accélèrent les lookups)
 */
return new class extends Migration
{
    /**
     * Crée la table doctors.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();

            // Référence à l'utilisateur propriétaire (1 user → 1 doctor max)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Référence à la spécialité médicale du médecin
            $table->foreignId('speciality_id')
                  ->constrained('specialities')
                  ->onDelete('cascade');

            // Informations de contact professionnelles
            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            // Biographie / description du médecin
            $table->text('bio')->nullable();

            // Chemin de la photo de profil (stockée dans storage/app/public/doctors/)
            $table->string('photo')->nullable();

            // Soft-delete pour préserver l'historique des rendez-vous
            $table->softDeletes();
            $table->timestamps();

            // Index pour accélérer les recherches par spécialité et par utilisateur
            $table->index('speciality_id');
            $table->index('user_id');
        });
    }

    /**
     * Supprime la table doctors.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
