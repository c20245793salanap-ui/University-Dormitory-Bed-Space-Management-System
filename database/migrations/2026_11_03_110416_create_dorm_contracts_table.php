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
        Schema::create('dorm_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('bed_slot_id')->constrained('bed_slots');
            $table->decimal('monthly_rate', 10, 2);
            $table->date('contract_start');
            $table->date('contract_end');
            $table->decimal('security_deposit', 10, 2);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->string('lease_status', 20)->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dorm_contracts');
    }
};
