<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A clinic's stance on one platform-wide holiday: whether it greets on that
     * day at all, and the text it uses instead of the admin default.
     *
     * A missing row means "not decided yet" and falls back to the holiday's own
     * defaults, so nothing has to be pre-filled for every clinic.
     */
    public function up(): void
    {
        Schema::create('clinic_holiday_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->text('template')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'holiday_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_holiday_settings');
    }
};
