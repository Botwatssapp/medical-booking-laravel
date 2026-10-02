<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Empêche la suppression physique d'une spécialité encore référencée.
 *
 * Le métier exige une spécialité obligatoire : SET NULL est exclu.
 * L'admin continue d'utiliser le soft-delete (SpecialtyController),
 * qui ne déclenche pas cette FK. RESTRICT protège forceDelete / SQL brut.
 */
return new class extends Migration
{
    public function up(): void
    {
        $orphans = DB::table('doctors')
            ->leftJoin('specialities', 'specialities.id', '=', 'doctors.speciality_id')
            ->whereNull('specialities.id')
            ->count();

        if ($orphans > 0) {
            throw new RuntimeException(
                "CONSTRAINT BLOCKED\n".
                "Table: doctors\n".
                "Column: speciality_id\n".
                "Existing violation: doctors rows reference a missing speciality\n".
                "Number of affected rows: {$orphans}\n".
                'Recommended manual remediation: assign a valid speciality_id to those doctors, then re-run the migration.'
            );
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['speciality_id']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->foreign('speciality_id')
                ->references('id')
                ->on('specialities')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['speciality_id']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->foreign('speciality_id')
                ->references('id')
                ->on('specialities')
                ->cascadeOnDelete();
        });
    }
};
