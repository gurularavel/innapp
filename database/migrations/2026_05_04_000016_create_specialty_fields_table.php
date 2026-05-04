<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialty_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('label', 255);
            $table->enum('type', ['text', 'number', 'date', 'select', 'textarea', 'photo'])->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['specialty_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_fields');
    }
};
