<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A date a greeting goes out on.
     *
     * `clinic_id` null  → platform-wide holiday curated by the admin; every
     *                     clinic sees it and may enable it or override its text.
     * `clinic_id` set   → the clinic's own date (an anniversary, a local feast).
     *
     * Most holidays sit on the same day every year, so only month/day are
     * stored. `year` is filled in only for moving dates — Ramazan and Qurban
     * bayramı shift each year and get one row per year.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('day');
            $table->unsignedSmallInteger('year')->nullable()
                ->comment('null = repeats every year; set = only that year');
            $table->text('template')->nullable()
                ->comment('Default text; the clinic may override it');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['month', 'day', 'is_active']);
            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
