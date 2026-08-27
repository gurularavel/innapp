<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_visit_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_visit_id')->constrained()->cascadeOnDelete();
            // FDI two-digit notation: 11-48 permanent, 51-85 primary.
            $table->unsignedTinyInteger('tooth_number');
            $table->string('status', 20)->default('treated');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['patient_visit_id', 'tooth_number']);
            $table->index('tooth_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_visit_teeth');
    }
};
