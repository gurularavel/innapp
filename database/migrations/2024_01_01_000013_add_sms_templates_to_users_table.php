<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('sms_appointment_template')->nullable()->after('phone');
            $table->text('sms_reminder_template')->nullable()->after('sms_appointment_template');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sms_appointment_template', 'sms_reminder_template']);
        });
    }
};
