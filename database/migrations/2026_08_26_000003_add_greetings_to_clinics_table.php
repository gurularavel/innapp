<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Greetings are opt-in: a clinic must switch them on itself, so no patient
     * ever receives an unsolicited message because of a platform default.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->text('sms_birthday_template')->nullable()->after('sms_reminder_template');
            $table->boolean('birthday_greetings_enabled')->default(false)->after('sms_birthday_template');
            $table->boolean('holiday_greetings_enabled')->default(false)->after('birthday_greetings_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'sms_birthday_template',
                'birthday_greetings_enabled',
                'holiday_greetings_enabled',
            ]);
        });
    }
};
