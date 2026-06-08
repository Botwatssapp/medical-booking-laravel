<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('gender');
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('birth_date');
            $table->decimal('weight', 5, 2)->nullable()->after('blood_type');
            $table->decimal('height', 5, 2)->nullable()->after('weight');
            $table->string('address')->nullable()->after('height');
            $table->string('emergency_contact')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'gender', 'birth_date', 'blood_type', 'weight', 'height', 'address', 'emergency_contact']);
        });
    }
};
