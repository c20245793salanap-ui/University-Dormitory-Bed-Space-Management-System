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
        Schema::create('dorm_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dorm_building_id')->constrained('dorm_buildings');
            $table->string('room_number', 30);
            $table->unique(['dorm_building_id', 'room_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dorm_rooms');
    }
};
