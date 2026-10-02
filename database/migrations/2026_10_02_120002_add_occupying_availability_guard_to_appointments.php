<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Barrière SQL : un seul rendez-vous occupant par availability_id.
 *
 * MySQL n'a pas d'index unique partiel (WHERE). SQLite si.
 * Colonne générée stockée + UNIQUE : portable MySQL 5.7+ / SQLite 3.31+.
 *
 * occupying_availability_id =
 *   availability_id si deleted_at IS NULL
 *   ET status IN (pending, accepted, completed, missed)
 *   sinon NULL
 *
 * Plusieurs NULL sont autorisés : historique cancelled/rejected possible.
 * Ne change pas les transitions applicatives.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('appointments')
            ->whereNull('deleted_at')
            ->whereIn('status', ['pending', 'accepted', 'completed', 'missed'])
            ->whereNotNull('availability_id')
            ->select('availability_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('availability_id')
            ->having('aggregate', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $ids = $duplicates->pluck('availability_id')->implode(', ');

            throw new RuntimeException(
                "CONSTRAINT BLOCKED\n".
                "Table: appointments\n".
                "Column: availability_id (occupying statuses)\n".
                "Existing violation: several occupying appointments share the same availability_id\n".
                'Number of affected rows: '.$duplicates->count()." availability_id group(s) ({$ids})\n".
                'Recommended manual remediation: inspect those slots, keep a single occupying appointment per slot (pending/accepted/completed/missed), then re-run the migration. Do not auto-cancel or merge.'
            );
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('occupying_availability_id')
                ->nullable()
                ->storedAs("CASE WHEN deleted_at IS NULL AND status IN ('pending', 'accepted', 'completed', 'missed') THEN availability_id ELSE NULL END");

            $table->unique('occupying_availability_id', 'appointments_occupying_availability_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_occupying_availability_id_unique');
            $table->dropColumn('occupying_availability_id');
        });
    }
};
