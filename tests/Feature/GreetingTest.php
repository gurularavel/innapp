<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ClinicHolidaySetting;
use App\Models\DoctorSubscription;
use App\Models\Holiday;
use App\Models\Package;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\GreetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreetingTest extends TestCase
{
    use RefreshDatabase;

    private function greetings(): GreetingService
    {
        return app(GreetingService::class);
    }

    private function clinic(array $overrides = []): Clinic
    {
        static $n = 0;
        $n++;

        $clinic = Clinic::create(array_merge([
            'name'                       => 'Test Klinika',
            'is_active'                  => true,
            'birthday_greetings_enabled' => true,
            'holiday_greetings_enabled'  => true,
        ], $overrides));

        $owner = User::create([
            'clinic_id' => $clinic->id,
            'name'      => 'Sahib' . $n,
            'surname'   => 'Sahibov',
            'email'     => "owner{$n}@test.local",
            'password'  => 'secret123',
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $clinic->update(['owner_id' => $owner->id]);

        $package = Package::firstOrCreate(
            ['name' => 'Standart'],
            [
                'price_per_seat' => 20,
                'min_seats'      => 1,
                'duration_days'  => 30,
                'is_active'      => true,
            ]
        );

        DoctorSubscription::create([
            'clinic_id'      => $clinic->id,
            'package_id'     => $package->id,
            'seats'          => 1,
            'price_per_seat' => 20,
            'starts_at'      => now()->toDateString(),
            'expires_at'     => now()->addDays(30)->toDateString(),
            'patients_used'  => 0,
            'is_active'      => true,
        ]);

        return $clinic->fresh();
    }

    private function patient(Clinic $clinic, array $overrides = []): Patient
    {
        static $n = 0;
        $n++;

        return Patient::create(array_merge([
            'clinic_id'  => $clinic->id,
            'name'       => 'Müştəri' . $n,
            'surname'    => 'Soyadov',
            'phone'      => '05512345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'birth_date' => '1990-05-12',
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // Birthdays
    // ---------------------------------------------------------------------

    public function test_patient_with_a_birthday_today_is_greeted(): void
    {
        $clinic  = $this->clinic();
        $patient = $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $stats = $this->greetings()->sendBirthdayGreetings($clinic, now());

        $this->assertSame(1, $stats['sent']);
        $this->assertDatabaseHas('sms_logs', [
            'patient_id' => $patient->id,
            'type'       => 'birthday',
            'status'     => 'sent',
        ]);
    }

    public function test_patient_with_another_birthday_is_left_alone(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->addDays(3)->subYears(30)->toDateString()]);

        $this->assertSame(0, $this->greetings()->sendBirthdayGreetings($clinic, now())['sent']);
    }

    public function test_nothing_is_sent_while_birthday_greetings_are_off(): void
    {
        $clinic = $this->clinic(['birthday_greetings_enabled' => false]);
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $this->assertSame(0, $this->greetings()->sendBirthdayGreetings($clinic, now())['sent']);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_a_patient_is_never_greeted_twice_on_the_same_day(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $this->greetings()->sendBirthdayGreetings($clinic, now());
        $second = $this->greetings()->sendBirthdayGreetings($clinic, now());

        $this->assertSame(0, $second['sent']);
        $this->assertSame(1, SmsLog::where('type', 'birthday')->count());
    }

    public function test_patients_without_a_phone_number_are_skipped(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString(), 'phone' => '']);

        $this->assertSame(0, $this->greetings()->sendBirthdayGreetings($clinic, now())['sent']);
    }

    public function test_a_leap_day_birthday_is_greeted_on_28_february_in_a_common_year(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => '1996-02-29']);

        // 2027 is not a leap year, so 29 February never arrives.
        $stats = $this->greetings()->sendBirthdayGreetings($clinic, now()->setDate(2027, 2, 28));

        $this->assertSame(1, $stats['sent']);
    }

    // ---------------------------------------------------------------------
    // Templates
    // ---------------------------------------------------------------------

    public function test_the_clinic_template_wins_over_the_global_default(): void
    {
        Setting::set('sms_birthday_template', 'Defolt mətn');

        $clinic = $this->clinic(['sms_birthday_template' => '{ad_soyad}, {yas} yaşınız mübarək! {muessise}']);
        $this->patient($clinic, [
            'name'       => 'Nigar',
            'surname'    => 'Əliyeva',
            'birth_date' => now()->subYears(30)->toDateString(),
        ]);

        $this->greetings()->sendBirthdayGreetings($clinic, now());

        $this->assertSame(
            'Nigar Əliyeva, 30 yaşınız mübarək! Test Klinika',
            SmsLog::where('type', 'birthday')->value('message')
        );
    }

    public function test_the_global_default_is_used_when_the_clinic_has_no_template(): void
    {
        Setting::set('sms_birthday_template', 'Salam {ad}, ad gününüz mübarək!');

        $clinic = $this->clinic();
        $this->patient($clinic, ['name' => 'Tural', 'birth_date' => now()->subYears(30)->toDateString()]);

        $this->greetings()->sendBirthdayGreetings($clinic, now());

        $this->assertSame('Salam Tural, ad gününüz mübarək!', SmsLog::where('type', 'birthday')->value('message'));
    }

    // ---------------------------------------------------------------------
    // Holidays
    // ---------------------------------------------------------------------

    private function sharedHoliday(array $overrides = []): Holiday
    {
        return Holiday::create(array_merge([
            'clinic_id' => null,
            'name'      => 'Novruz bayramı',
            'month'     => now()->month,
            'day'       => now()->day,
            'is_active' => true,
        ], $overrides));
    }

    public function test_a_shared_holiday_greets_every_reachable_patient(): void
    {
        Setting::set('sms_holiday_template', '{bayram} mübarək, {ad_soyad}!');

        $clinic = $this->clinic();
        $this->patient($clinic, ['name' => 'Elnur', 'surname' => 'Quliyev']);
        $this->patient($clinic);
        $this->sharedHoliday();

        $stats = $this->greetings()->sendHolidayGreetings($clinic, now());

        $this->assertSame(2, $stats['sent']);
        $this->assertDatabaseHas('sms_logs', [
            'type'    => 'holiday',
            'message' => 'Novruz bayramı mübarək, Elnur Quliyev!',
        ]);
    }

    public function test_a_clinic_can_switch_one_shared_holiday_off(): void
    {
        $clinic  = $this->clinic();
        $holiday = $this->sharedHoliday();
        $this->patient($clinic);

        ClinicHolidaySetting::create([
            'clinic_id'  => $clinic->id,
            'holiday_id' => $holiday->id,
            'is_enabled' => false,
        ]);

        $this->assertSame(0, $this->greetings()->sendHolidayGreetings($clinic, now())['sent']);
    }

    public function test_a_clinic_override_replaces_the_holiday_text(): void
    {
        $clinic  = $this->clinic();
        $holiday = $this->sharedHoliday(['template' => 'Admin mətni']);
        $this->patient($clinic);

        ClinicHolidaySetting::create([
            'clinic_id'  => $clinic->id,
            'holiday_id' => $holiday->id,
            'is_enabled' => true,
            'template'   => 'Klinikanın öz mətni — {bayram}',
        ]);

        $this->greetings()->sendHolidayGreetings($clinic, now());

        $this->assertSame(
            'Klinikanın öz mətni — Novruz bayramı',
            SmsLog::where('type', 'holiday')->value('message')
        );
    }

    public function test_the_holiday_specific_admin_text_beats_the_global_default(): void
    {
        Setting::set('sms_holiday_template', 'Ümumi mətn');

        $clinic = $this->clinic();
        $this->sharedHoliday(['template' => 'Bu bayrama xüsusi mətn']);
        $this->patient($clinic);

        $this->greetings()->sendHolidayGreetings($clinic, now());

        $this->assertSame('Bu bayrama xüsusi mətn', SmsLog::where('type', 'holiday')->value('message'));
    }

    public function test_a_clinic_own_date_is_invisible_to_other_clinics(): void
    {
        $mine  = $this->clinic();
        $other = $this->clinic();

        Holiday::create([
            'clinic_id' => $mine->id,
            'name'      => 'Klinikanın yubileyi',
            'month'     => now()->month,
            'day'       => now()->day,
            'is_active' => true,
        ]);

        $this->patient($mine);
        $this->patient($other);

        $this->assertSame(1, $this->greetings()->sendHolidayGreetings($mine, now())['sent']);
        $this->assertSame(0, $this->greetings()->sendHolidayGreetings($other, now())['sent']);
    }

    public function test_a_date_pinned_to_one_year_does_not_fire_in_another(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic);

        $this->sharedHoliday(['name' => 'Ramazan bayramı', 'year' => now()->year + 1]);

        $this->assertSame(0, $this->greetings()->sendHolidayGreetings($clinic, now())['sent']);
        $this->assertSame(1, $this->greetings()->sendHolidayGreetings($clinic, now()->addYear())['sent']);
    }

    public function test_a_deactivated_holiday_never_fires(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic);
        $this->sharedHoliday(['is_active' => false]);

        $this->assertSame(0, $this->greetings()->sendHolidayGreetings($clinic, now())['sent']);
    }

    // ---------------------------------------------------------------------
    // Eligibility
    // ---------------------------------------------------------------------

    public function test_a_clinic_without_an_active_subscription_is_not_eligible(): void
    {
        $clinic = $this->clinic();
        $clinic->subscriptions()->update(['is_active' => false]);

        $this->assertFalse($this->greetings()->eligibleClinics()->whereKey($clinic->id)->exists());
    }

    public function test_a_demo_clinic_is_never_eligible(): void
    {
        $clinic = $this->clinic();
        $clinic->owner->update(['is_demo' => true, 'demo_expires_at' => now()->addHours(2)]);

        $this->assertFalse($this->greetings()->eligibleClinics()->whereKey($clinic->id)->exists());
    }

    public function test_a_clinic_with_greetings_switched_off_entirely_is_not_eligible(): void
    {
        $clinic = $this->clinic([
            'birthday_greetings_enabled' => false,
            'holiday_greetings_enabled'  => false,
        ]);

        $this->assertFalse($this->greetings()->eligibleClinics()->whereKey($clinic->id)->exists());
    }

    // ---------------------------------------------------------------------
    // Command
    // ---------------------------------------------------------------------

    public function test_the_command_does_nothing_outside_the_configured_hour(): void
    {
        Setting::set('greetings_send_hour', (string) now()->addHours(3)->hour);

        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $this->artisan('greetings:send')->assertSuccessful();

        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_the_command_sends_when_forced(): void
    {
        Setting::set('greetings_send_hour', (string) now()->addHours(3)->hour);

        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $this->artisan('greetings:send', ['--force' => true])->assertSuccessful();

        $this->assertSame(1, SmsLog::where('type', 'birthday')->count());
    }

    public function test_a_dry_run_sends_nothing(): void
    {
        $clinic = $this->clinic();
        $this->patient($clinic, ['birth_date' => now()->subYears(30)->toDateString()]);

        $this->artisan('greetings:send', ['--force' => true, '--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('sms_logs', 0);
    }

    // ---------------------------------------------------------------------
    // Panel
    // ---------------------------------------------------------------------

    public function test_owner_can_save_greeting_settings(): void
    {
        $clinic  = $this->clinic(['birthday_greetings_enabled' => false]);
        $holiday = $this->sharedHoliday();

        $this->actingAs($clinic->owner)
            ->put(route('panel.greetings.save'), [
                'birthday_greetings_enabled' => '1',
                'sms_birthday_template'      => 'Ad gününüz mübarək, {ad}!',
                'holidays'                   => [
                    $holiday->id => ['template' => 'Xüsusi mətn'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('panel.greetings.index'));

        $clinic->refresh();

        $this->assertTrue($clinic->birthday_greetings_enabled);
        $this->assertSame('Ad gününüz mübarək, {ad}!', $clinic->sms_birthday_template);

        // The checkbox was absent, so the holiday must come out switched off.
        $this->assertDatabaseHas('clinic_holiday_settings', [
            'clinic_id'  => $clinic->id,
            'holiday_id' => $holiday->id,
            'is_enabled' => false,
        ]);
    }

    public function test_the_greetings_page_renders_for_the_owner(): void
    {
        $clinic = $this->clinic();
        $this->sharedHoliday();

        Holiday::create([
            'clinic_id' => $clinic->id,
            'name'      => 'Klinikanın yubileyi',
            'month'     => 6,
            'day'       => 1,
            'is_active' => true,
        ]);

        $this->actingAs($clinic->owner)
            ->get(route('panel.greetings.index'))
            ->assertOk()
            ->assertSee('Ad Günü Təbriki')
            ->assertSee('Klinikanın yubileyi');
    }

    public function test_the_admin_screens_render(): void
    {
        $admin = User::create([
            'name'      => 'Sistem',
            'surname'   => 'Admini',
            'email'     => 'admin@test.local',
            'password'  => 'secret123',
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $this->sharedHoliday();

        $this->actingAs($admin)->get(route('admin.settings.greetings'))->assertOk();
        $this->actingAs($admin)->get(route('admin.holidays.index'))->assertOk()->assertSee('Novruz');
        $this->actingAs($admin)->get(route('admin.holidays.create'))->assertOk();
    }

    public function test_a_specialist_cannot_change_greeting_settings(): void
    {
        $clinic = $this->clinic();

        $doctor = User::create([
            'clinic_id' => $clinic->id,
            'name'      => 'Mütəxəssis',
            'surname'   => 'Həkimov',
            'email'     => 'hekim@test.local',
            'password'  => 'secret123',
            'role'      => 'doctor',
            'is_active' => true,
        ]);

        $this->actingAs($doctor)
            ->put(route('panel.greetings.save'), ['birthday_greetings_enabled' => '1'])
            ->assertForbidden();
    }

    public function test_a_clinic_cannot_edit_the_platform_wide_calendar(): void
    {
        $clinic  = $this->clinic();
        $holiday = $this->sharedHoliday();

        $this->actingAs($clinic->owner)
            ->delete(route('panel.greetings.holidays.destroy', $holiday))
            ->assertNotFound();

        $this->assertDatabaseHas('holidays', ['id' => $holiday->id]);
    }
}
