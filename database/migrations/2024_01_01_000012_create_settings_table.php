<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default SMS templates
        DB::table('settings')->insert([
            [
                'key'        => 'sms_appointment_template',
                'value'      => 'Hörmətli {ad_soyad}, {tarix} {saat} tarixində {xidmet} xidməti üçün randevunuz təsdiqləndi. {klinika}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'sms_reminder_template',
                'value'      => 'Xatırlatma: {ad_soyad}, {tarix} {saat} tarixində {xidmet} randevunuz var. {klinika}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'clinic_name',
                'value'      => 'Klinikamız',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
