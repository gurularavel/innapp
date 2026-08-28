<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-clinic WhatsApp Cloud API connection.
 *
 * Until now WhatsApp could only be set up platform-wide by the admin, so every
 * clinic sent from the same number. A clinic that fills these in sends from its
 * own WhatsApp Business number instead; the admin values stay as the fallback
 * for everyone who has not connected their own.
 *
 * Template names are per-clinic too, because an approved template belongs to
 * the WhatsApp Business Account it was approved on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('whatsapp_enabled')->default(false)->after('notify_channel');
            $table->string('whatsapp_number', 20)->nullable()->after('whatsapp_enabled');
            $table->string('whatsapp_phone_number_id', 64)->nullable()->after('whatsapp_number');
            $table->text('whatsapp_access_token')->nullable()->after('whatsapp_phone_number_id');
            $table->string('whatsapp_language_code', 10)->nullable()->after('whatsapp_access_token');

            $table->string('whatsapp_appointment_template', 100)->nullable()->after('whatsapp_language_code');
            $table->string('whatsapp_appointment_params', 255)->nullable()->after('whatsapp_appointment_template');
            $table->string('whatsapp_reminder_template', 100)->nullable()->after('whatsapp_appointment_params');
            $table->string('whatsapp_reminder_params', 255)->nullable()->after('whatsapp_reminder_template');
            $table->string('whatsapp_birthday_template', 100)->nullable()->after('whatsapp_reminder_params');
            $table->string('whatsapp_birthday_params', 255)->nullable()->after('whatsapp_birthday_template');
            $table->string('whatsapp_holiday_template', 100)->nullable()->after('whatsapp_birthday_params');
            $table->string('whatsapp_holiday_params', 255)->nullable()->after('whatsapp_holiday_template');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'whatsapp_number',
                'whatsapp_phone_number_id',
                'whatsapp_access_token',
                'whatsapp_language_code',
                'whatsapp_appointment_template',
                'whatsapp_appointment_params',
                'whatsapp_reminder_template',
                'whatsapp_reminder_params',
                'whatsapp_birthday_template',
                'whatsapp_birthday_params',
                'whatsapp_holiday_template',
                'whatsapp_holiday_params',
            ]);
        });
    }
};
