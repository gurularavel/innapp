<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationChannelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'surname' => 'User',
            'email' => 'admin@test.local', 'password' => 'secret123',
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    /** A clinic owner — messaging settings are clinic-wide and owner-managed. */
    private function doctor(string $role = 'owner'): User
    {
        $clinic = Clinic::create(['name' => 'Test Klinika', 'is_active' => true]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Həkim', 'surname' => 'Test',
            'email' => 'doctor@test.local', 'password' => 'secret123',
            'role' => $role, 'takes_appointments' => true, 'is_active' => true,
        ]);

        $clinic->update(['owner_id' => $user->id]);

        return $user;
    }

    private function enableWhatsapp(): void
    {
        Setting::set('whatsapp_enabled', '1');
        Setting::set('whatsapp_phone_number_id', '123456789');
        Setting::set('whatsapp_access_token', encrypt('token'));
    }

    public function test_new_clinic_defaults_to_sms_only(): void
    {
        $this->assertSame('sms', $this->doctor()->clinic->notify_channel);
    }

    public function test_admin_can_open_whatsapp_settings(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.settings.whatsapp'))
            ->assertOk()
            ->assertSee('whatsapp_phone_number_id', false);
    }

    public function test_whatsapp_cannot_be_enabled_without_credentials(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.whatsapp.save'), [
                'whatsapp_enabled'      => '1',
                'whatsapp_api_version'  => 'v21.0',
                'whatsapp_language_code' => 'az',
            ])
            ->assertSessionHasErrors('whatsapp_enabled');

        $this->assertSame('0', Setting::get('whatsapp_enabled'));
    }

    public function test_admin_saves_settings_and_token_is_encrypted(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.whatsapp.save'), [
                'whatsapp_enabled'              => '1',
                'whatsapp_api_version'          => 'v21.0',
                'whatsapp_phone_number_id'      => '999888777',
                'whatsapp_access_token'         => 'PLAIN_TOKEN',
                'whatsapp_language_code'        => 'az',
                'whatsapp_reminder_template'    => 'randevu_xatirlatma',
                'whatsapp_reminder_params'      => '{ad_soyad},{tarix}',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('1', Setting::get('whatsapp_enabled'));
        $stored = Setting::get('whatsapp_access_token');
        $this->assertNotSame('PLAIN_TOKEN', $stored);
        $this->assertSame('PLAIN_TOKEN', decrypt($stored));
        $this->assertTrue(app(WhatsAppService::class)->isConfigured());
    }

    public function test_doctor_sees_all_three_channel_options(): void
    {
        $this->enableWhatsapp();

        $this->actingAs($this->doctor())
            ->get(route('panel.sms-templates.index'))
            ->assertOk()
            ->assertSee('Yalnız SMS')
            ->assertSee('Yalnız WhatsApp')
            ->assertSee('SMS + WhatsApp')
            ->assertDontSee('WhatsApp hazırda aktiv deyil');
    }

    public function test_doctor_sees_a_notice_when_whatsapp_is_off(): void
    {
        $this->actingAs($this->doctor())
            ->get(route('panel.sms-templates.index'))
            ->assertOk()
            ->assertSee('WhatsApp hazırda aktiv deyil');
    }

    public function test_doctor_can_switch_to_both_channels_when_whatsapp_is_on(): void
    {
        $this->enableWhatsapp();
        $doctor = $this->doctor();

        $this->actingAs($doctor)
            ->put(route('panel.sms-templates.save'), [
                'notify_channel'           => 'both',
                'sms_appointment_template' => 'Salam {ad_soyad}',
                'sms_reminder_template'    => 'Xatırlatma {ad_soyad}',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('both', $doctor->clinic->fresh()->notify_channel);
        $this->assertSame(
            ['sms', 'whatsapp'],
            app(\App\Services\NotificationService::class)->channelsFor($doctor->fresh())
        );
    }

    public function test_doctor_cannot_pick_whatsapp_while_it_is_disabled(): void
    {
        $doctor = $this->doctor();

        $this->actingAs($doctor)
            ->put(route('panel.sms-templates.save'), ['notify_channel' => 'whatsapp'])
            ->assertSessionHasErrors('notify_channel');

        $this->assertSame('sms', $doctor->clinic->fresh()->notify_channel);
    }

    public function test_whatsapp_preference_falls_back_to_sms_when_not_configured(): void
    {
        $doctor = $this->doctor();
        $doctor->clinic->update(['notify_channel' => 'both']);

        $channels = app(\App\Services\NotificationService::class)->channelsFor($doctor->fresh());

        $this->assertSame(['sms'], $channels);
    }

    public function test_both_channels_are_used_once_whatsapp_is_configured(): void
    {
        $this->enableWhatsapp();
        $doctor = $this->doctor();
        $doctor->clinic->update(['notify_channel' => 'both']);

        $channels = app(\App\Services\NotificationService::class)->channelsFor($doctor->fresh());

        $this->assertSame(['sms', 'whatsapp'], $channels);
    }

    // -------------------------------------------------------------------------
    // A clinic's own WhatsApp connection
    // -------------------------------------------------------------------------

    public function test_owner_can_connect_their_own_whatsapp_number(): void
    {
        $owner = $this->doctor();

        $this->actingAs($owner)
            ->put(route('panel.whatsapp.save'), [
                'whatsapp_enabled'           => '1',
                'whatsapp_number'            => '994551234567',
                'whatsapp_phone_number_id'   => '111222333',
                'whatsapp_access_token'      => 'CLINIC_TOKEN',
                'whatsapp_reminder_template' => 'klinika_xatirlatma',
            ])
            ->assertSessionHasNoErrors();

        $clinic = $owner->clinic->fresh();

        $this->assertTrue($clinic->hasOwnWhatsapp());
        $this->assertNotSame('CLINIC_TOKEN', $clinic->whatsapp_access_token);
        $this->assertSame('CLINIC_TOKEN', decrypt($clinic->whatsapp_access_token));

        // Own credentials make WhatsApp usable even with the platform one off.
        $this->assertTrue(app(WhatsAppService::class)->isConfiguredFor($clinic));
        $this->assertFalse(app(WhatsAppService::class)->isConfigured());
    }

    public function test_own_connection_cannot_be_enabled_without_credentials(): void
    {
        $owner = $this->doctor();

        $this->actingAs($owner)
            ->put(route('panel.whatsapp.save'), ['whatsapp_enabled' => '1'])
            ->assertSessionHasErrors('whatsapp_enabled');

        $this->assertFalse($owner->clinic->fresh()->whatsapp_enabled);
    }

    public function test_clinic_credentials_and_templates_beat_the_platform_ones(): void
    {
        $this->enableWhatsapp();
        Setting::set('whatsapp_reminder_template', 'platforma_sablonu');

        $clinic = $this->doctor()->clinic;
        $clinic->update([
            'whatsapp_enabled'           => true,
            'whatsapp_phone_number_id'   => '111222333',
            'whatsapp_access_token'      => encrypt('CLINIC_TOKEN'),
            'whatsapp_language_code'     => 'tr',
            'whatsapp_reminder_template' => 'klinika_sablonu',
        ]);

        $whatsapp = app(WhatsAppService::class);
        $clinic   = $clinic->fresh();
        $config   = $whatsapp->configFor($clinic);

        $this->assertSame('clinic', $config['source']);
        $this->assertSame('111222333', $config['phone_number_id']);
        $this->assertSame('CLINIC_TOKEN', $config['access_token']);
        $this->assertSame('tr', $config['language_code']);
        $this->assertSame('klinika_sablonu', $whatsapp->templateFor($clinic, 'reminder'));
    }

    public function test_a_clinic_without_its_own_connection_uses_the_platform_one(): void
    {
        $this->enableWhatsapp();
        Setting::set('whatsapp_reminder_template', 'platforma_sablonu');

        $clinic   = $this->doctor()->clinic;
        $whatsapp = app(WhatsAppService::class);

        $this->assertSame('admin', $whatsapp->configFor($clinic)['source']);
        $this->assertSame('platforma_sablonu', $whatsapp->templateFor($clinic, 'reminder'));
    }

    public function test_disconnecting_falls_back_to_the_platform_connection(): void
    {
        $this->enableWhatsapp();
        $owner  = $this->doctor();
        $clinic = $owner->clinic;

        $clinic->update([
            'whatsapp_enabled'         => true,
            'whatsapp_phone_number_id' => '111222333',
            'whatsapp_access_token'    => encrypt('CLINIC_TOKEN'),
        ]);

        $this->actingAs($owner)
            ->delete(route('panel.whatsapp.disconnect'))
            ->assertSessionHasNoErrors();

        $clinic = $clinic->fresh();

        $this->assertFalse($clinic->hasOwnWhatsapp());
        $this->assertNull($clinic->whatsapp_access_token);
        $this->assertSame('admin', app(WhatsAppService::class)->configFor($clinic)['source']);
    }

    public function test_owner_can_pick_whatsapp_with_only_their_own_connection(): void
    {
        $owner = $this->doctor();
        $owner->clinic->update([
            'whatsapp_enabled'         => true,
            'whatsapp_phone_number_id' => '111222333',
            'whatsapp_access_token'    => encrypt('CLINIC_TOKEN'),
        ]);

        $this->actingAs($owner)
            ->put(route('panel.sms-templates.save'), ['notify_channel' => 'whatsapp'])
            ->assertSessionHasNoErrors();

        $this->assertSame('whatsapp', $owner->clinic->fresh()->notify_channel);
        $this->assertSame(['whatsapp'], app(NotificationService::class)->channelsFor($owner->fresh()));
    }

    public function test_a_specialist_cannot_open_the_whatsapp_connection_screen(): void
    {
        $owner = $this->doctor();

        $specialist = User::create([
            'clinic_id' => $owner->clinic_id,
            'name' => 'Mütəxəssis', 'surname' => 'Test',
            'email' => 'spec-wa@test.local', 'password' => 'secret123',
            'role' => 'doctor', 'takes_appointments' => true, 'is_active' => true,
        ]);

        $this->actingAs($specialist)
            ->get(route('panel.whatsapp.edit'))
            ->assertForbidden();
    }

    public function test_the_whatsapp_connection_screen_renders_for_the_owner(): void
    {
        $this->actingAs($this->doctor())
            ->get(route('panel.whatsapp.edit'))
            ->assertOk()
            ->assertSee('whatsapp_phone_number_id', false)
            ->assertSee('Öz WhatsApp bağlantımı işlət');
    }

    public function test_a_specialist_cannot_change_clinic_wide_messaging(): void
    {
        $owner  = $this->doctor();
        $clinic = $owner->clinic;

        $specialist = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Mütəxəssis', 'surname' => 'Test',
            'email' => 'spec@test.local', 'password' => 'secret123',
            'role' => 'doctor', 'takes_appointments' => true, 'is_active' => true,
        ]);

        $this->actingAs($specialist)
            ->put(route('panel.sms-templates.save'), ['notify_channel' => 'sms'])
            ->assertForbidden();
    }
}
