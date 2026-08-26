<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A clinic is the tenant every account belongs to. A solo specialist is
     * simply a clinic with one member, so both use the exact same structures.
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();

            // Short map link, previously users.muessise_xerite / _code
            $table->string('map_url', 2000)->nullable();
            $table->string('map_code', 16)->nullable()->unique();

            // Messaging identity is shared across the whole clinic
            $table->enum('notify_channel', ['sms', 'whatsapp', 'both'])->default('sms');
            $table->text('sms_appointment_template')->nullable();
            $table->text('sms_reminder_template')->nullable();
            $table->boolean('sms_copy_to_self')->default(false);

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
