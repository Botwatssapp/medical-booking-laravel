<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garantit au niveau SQL qu'un utilisateur n'a qu'un seul profil médecin.
 *
 * MySQL/MariaDB : l'index non unique `doctors_user_id_index` peut servir
 * à la FK `doctors_user_id_foreign`. Il ne peut pas être droppé tant
 * que la FK existe. On retire la FK, on remplace l'index, on rétablit la FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('doctors')
            ->select('user_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('user_id')
            ->having('aggregate', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $ids = $duplicates->pluck('user_id')->implode(', ');

            throw new RuntimeException(
                "CONSTRAINT BLOCKED\n".
                "Table: doctors\n".
                "Column: user_id\n".
                "Existing violation: several doctors rows share the same user_id\n".
                'Number of affected rows: '.$duplicates->count()." user_id group(s) ({$ids})\n".
                'Recommended manual remediation: inspect those user_id values, keep a single doctors row per user, then re-run the migration. Do not auto-merge.'
            );
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        $indexNames = collect(Schema::getIndexes('doctors'))->pluck('name');

        Schema::table('doctors', function (Blueprint $table) use ($indexNames) {
            if ($indexNames->contains('doctors_user_id_index')) {
                $table->dropIndex('doctors_user_id_index');
            }

            if (! $indexNames->contains('doctors_user_id_unique')) {
                $table->unique('user_id', 'doctors_user_id_unique');
            }

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique('doctors_user_id_unique');
            $table->index('user_id', 'doctors_user_id_index');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
