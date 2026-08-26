<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Platform defaults for greetings.
     *
     * The holiday list holds the recurring Azerbaijani public holidays that a
     * business would normally congratulate on. Days of mourning (20 Yanvar,
     * 31 Mart) are deliberately absent — a greeting on those dates would be
     * offensive. Moving religious holidays are left to the admin, who adds
     * them per year once the dates are announced.
     */
    private array $settings = [
        'greetings_send_hour'        => '9',
        'sms_birthday_template'      => 'Hörmətli {ad_soyad}, ad gününüz mübarək olsun! {muessise}',
        'sms_holiday_template'       => 'Hörmətli {ad_soyad}, {bayram} münasibətilə sizi təbrik edirik! {muessise}',
        'whatsapp_birthday_template' => '',
        'whatsapp_birthday_params'   => '{ad_soyad},{muessise}',
        'whatsapp_holiday_template'  => '',
        'whatsapp_holiday_params'    => '{ad_soyad},{bayram},{muessise}',
    ];

    /** [month, day, name] */
    private array $holidays = [
        [1,  1,  'Yeni il'],
        [3,  8,  'Beynəlxalq Qadınlar Günü'],
        [3,  20, 'Novruz bayramı'],
        [5,  9,  'Qələbə Günü'],
        [5,  28, 'Müstəqillik Günü'],
        [6,  15, 'Milli Qurtuluş Günü'],
        [6,  26, 'Silahlı Qüvvələr Günü'],
        [11, 8,  'Zəfər Günü'],
        [11, 9,  'Dövlət Bayrağı Günü'],
        [12, 31, 'Dünya Azərbaycanlılarının Həmrəylik Günü'],
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        foreach ($this->holidays as [$month, $day, $name]) {
            $exists = DB::table('holidays')
                ->whereNull('clinic_id')
                ->where('month', $month)
                ->where('day', $day)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('holidays')->insert([
                'clinic_id'  => null,
                'name'       => $name,
                'month'      => $month,
                'day'        => $day,
                'year'       => null,
                'template'   => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys($this->settings))->delete();
        DB::table('holidays')->whereNull('clinic_id')->delete();
    }
};
