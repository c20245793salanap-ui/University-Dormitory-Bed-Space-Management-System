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
        Schema::create('bed_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dorm_room_id')->constrained('dorm_rooms');
            $table->string('bed_slot', 20);
            $table->string('status', 20)->default('Vacant');
            $table->unique(['dorm_room_id', 'bed_slot']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_slots');
    }
};
