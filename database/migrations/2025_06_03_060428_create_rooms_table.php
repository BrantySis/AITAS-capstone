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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code'); // e.g., Room 101
            $table->string('building_name')->nullable(); // optional
            $table->decimal('latitude', 10, 7)->nullable(); // for geolocation
            $table->decimal('longitude', 10, 7)->nullable(); // for geolocation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
