<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index admin : filtre statut + tri par date
 * (AppointmentController@index, DashboardController).
 *
 * Ne duplique pas (patient_id, status) ni (doctor_id, status).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['status', 'appointment_date'], 'appointments_status_appointment_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_status_appointment_date_index');
        });
    }
};
