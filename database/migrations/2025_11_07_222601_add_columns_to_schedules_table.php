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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('day_of_week')->after('type');
            $table->string('school_year', 15)->after('ends_at');
            $table->enum('semester', ['1st', '2nd', 'summer'])->after('school_year');
            $table->date('start_date')->after('semester');
            $table->date('end_date')->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
             $table->dropColumn(['day_of_week', 'school_year', 'semester', 'start_date', 'end_date']);
        });
    }
};
