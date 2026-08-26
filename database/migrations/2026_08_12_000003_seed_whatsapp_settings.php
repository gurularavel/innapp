<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Default WhatsApp Cloud API settings. Credentials stay empty until the
     * admin fills them in from Ayarlar » WhatsApp.
     */
    private array $defaults = [
        'whatsapp_enabled'              => '0',
        'whatsapp_api_version'          => 'v21.0',
        'whatsapp_phone_number_id'      => '',
        'whatsapp_access_token'         => '',
        'whatsapp_language_code'        => 'az',
        'whatsapp_appointment_template' => '',
        'whatsapp_appointment_params'   => '{ad_soyad},{tarix},{saat}',
        'whatsapp_reminder_template'    => '',
        'whatsapp_reminder_params'      => '{ad_soyad},{tarix},{saat}',
    ];

    public function up(): void
    {
        foreach ($this->defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys($this->defaults))->delete();
    }
};
