<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Holiday;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function smsTemplates()
    {
        $appointmentTemplate  = Setting::get('sms_appointment_template', '');
        $reminderTemplate     = Setting::get('sms_reminder_template', '');
        $defaultMuessise      = Setting::get('default_muessise_adi', '');
        $reminderMinutesBefore = (int) Setting::get('reminder_minutes_before', 120);

        return view('admin.settings.sms-templates', compact(
            'appointmentTemplate',
            'reminderTemplate',
            'defaultMuessise',
            'reminderMinutesBefore'
        ));
    }

    public function saveSmsTemplates(Request $request)
    {
        $request->validate([
            'sms_appointment_template' => ['required', 'string', 'max:160'],
            'sms_reminder_template'    => ['required', 'string', 'max:160'],
            'default_muessise_adi'     => ['required', 'string', 'max:100'],
            'reminder_minutes_before'  => ['required', 'integer', 'min:5', 'max:2880'],
        ]);

        Setting::set('sms_appointment_template', $request->sms_appointment_template);
        Setting::set('sms_reminder_template',    $request->sms_reminder_template);
        Setting::set('default_muessise_adi',     $request->default_muessise_adi);
        Setting::set('reminder_minutes_before',  $request->reminder_minutes_before);

        return back()->with('success', 'SMS şablonları yadda saxlandı.');
    }

    public function whatsapp()
    {
        $settings = [
            'whatsapp_enabled'              => Setting::get('whatsapp_enabled', '0'),
            'whatsapp_api_version'          => Setting::get('whatsapp_api_version', 'v21.0'),
            'whatsapp_phone_number_id'      => Setting::get('whatsapp_phone_number_id', ''),
            'whatsapp_language_code'        => Setting::get('whatsapp_language_code', 'az'),
            'whatsapp_appointment_template' => Setting::get('whatsapp_appointment_template', ''),
            'whatsapp_appointment_params'   => Setting::get('whatsapp_appointment_params', '{ad_soyad},{tarix},{saat}'),
            'whatsapp_reminder_template'    => Setting::get('whatsapp_reminder_template', ''),
            'whatsapp_reminder_params'      => Setting::get('whatsapp_reminder_params', '{ad_soyad},{tarix},{saat}'),
            'whatsapp_birthday_template'    => Setting::get('whatsapp_birthday_template', ''),
            'whatsapp_birthday_params'      => Setting::get('whatsapp_birthday_params', '{ad_soyad},{muessise}'),
            'whatsapp_holiday_template'     => Setting::get('whatsapp_holiday_template', ''),
            'whatsapp_holiday_params'       => Setting::get('whatsapp_holiday_params', '{ad_soyad},{bayram},{muessise}'),
        ];

        $hasToken = (string) Setting::get('whatsapp_access_token', '') !== '';

        // How many clinics picked each channel — helps the admin see the impact.
        $channelUsage = \App\Models\Clinic::selectRaw('notify_channel, COUNT(*) as total')
            ->groupBy('notify_channel')
            ->pluck('total', 'notify_channel');

        return view('admin.settings.whatsapp', compact('settings', 'hasToken', 'channelUsage'));
    }

    public function saveWhatsapp(Request $request)
    {
        $request->validate([
            'whatsapp_enabled'              => ['boolean'],
            'whatsapp_api_version'          => ['required', 'string', 'max:10'],
            'whatsapp_phone_number_id'      => ['nullable', 'string', 'max:64'],
            'whatsapp_access_token'         => ['nullable', 'string', 'max:1000'],
            'whatsapp_language_code'        => ['required', 'string', 'max:10'],
            'whatsapp_appointment_template' => ['nullable', 'string', 'max:100'],
            'whatsapp_appointment_params'   => ['nullable', 'string', 'max:255'],
            'whatsapp_reminder_template'    => ['nullable', 'string', 'max:100'],
            'whatsapp_reminder_params'      => ['nullable', 'string', 'max:255'],
            'whatsapp_birthday_template'    => ['nullable', 'string', 'max:100'],
            'whatsapp_birthday_params'      => ['nullable', 'string', 'max:255'],
            'whatsapp_holiday_template'     => ['nullable', 'string', 'max:100'],
            'whatsapp_holiday_params'       => ['nullable', 'string', 'max:255'],
        ]);

        // Cannot switch the channel on without the credentials behind it.
        $enabled  = $request->boolean('whatsapp_enabled');
        $hasToken = $request->filled('whatsapp_access_token')
            || (string) Setting::get('whatsapp_access_token', '') !== '';

        if ($enabled && (!$request->filled('whatsapp_phone_number_id') || !$hasToken)) {
            return back()->withInput()->withErrors([
                'whatsapp_enabled' => 'WhatsApp-ı aktivləşdirmək üçün Phone Number ID və Access Token doldurulmalıdır.',
            ]);
        }

        Setting::set('whatsapp_enabled',              $enabled ? '1' : '0');
        Setting::set('whatsapp_api_version',          $request->whatsapp_api_version);
        Setting::set('whatsapp_phone_number_id',      $request->whatsapp_phone_number_id);
        Setting::set('whatsapp_language_code',        $request->whatsapp_language_code);
        Setting::set('whatsapp_appointment_template', $request->whatsapp_appointment_template);
        Setting::set('whatsapp_appointment_params',   $request->whatsapp_appointment_params);
        Setting::set('whatsapp_reminder_template',    $request->whatsapp_reminder_template);
        Setting::set('whatsapp_reminder_params',      $request->whatsapp_reminder_params);
        Setting::set('whatsapp_birthday_template',    $request->whatsapp_birthday_template);
        Setting::set('whatsapp_birthday_params',      $request->whatsapp_birthday_params);
        Setting::set('whatsapp_holiday_template',     $request->whatsapp_holiday_template);
        Setting::set('whatsapp_holiday_params',       $request->whatsapp_holiday_params);

        // Only overwrite the token when a new one is typed in.
        if ($request->filled('whatsapp_access_token')) {
            Setting::set('whatsapp_access_token', encrypt($request->whatsapp_access_token));
        }

        return back()->with('success', 'WhatsApp ayarları yadda saxlandı.');
    }

    /**
     * Platform defaults for birthday and holiday greetings: the hour they go
     * out and the wording a clinic gets until it writes its own.
     */
    public function greetings()
    {
        $settings = [
            'greetings_send_hour'   => (int) Setting::get('greetings_send_hour', 9),
            'sms_birthday_template' => Setting::get('sms_birthday_template', ''),
            'sms_holiday_template'  => Setting::get('sms_holiday_template', ''),
        ];

        // How many clinics have each greeting switched on — greetings are
        // opt-in, so this is the only measure of actual reach.
        $usage = [
            'clinics'  => Clinic::where('is_active', true)->count(),
            'birthday' => Clinic::where('birthday_greetings_enabled', true)->count(),
            'holiday'  => Clinic::where('holiday_greetings_enabled', true)->count(),
        ];

        $holidays = Holiday::shared()->active()->inCalendarOrder()->get();

        return view('admin.settings.greetings', compact('settings', 'usage', 'holidays'));
    }

    public function saveGreetings(Request $request)
    {
        $request->validate([
            'greetings_send_hour'   => ['required', 'integer', 'min:0', 'max:23'],
            'sms_birthday_template' => ['required', 'string', 'max:160'],
            'sms_holiday_template'  => ['required', 'string', 'max:160'],
        ]);

        Setting::set('greetings_send_hour',   $request->greetings_send_hour);
        Setting::set('sms_birthday_template', $request->sms_birthday_template);
        Setting::set('sms_holiday_template',  $request->sms_holiday_template);

        return back()->with('success', 'Təbrik ayarları yadda saxlandı.');
    }

    public function terms()
    {
        $termsTitle   = Setting::get('terms_title', 'İstifadə Qaydaları');
        $termsContent = Setting::get('terms_content', '');

        return view('admin.settings.terms', compact('termsTitle', 'termsContent'));
    }

    public function saveTerms(Request $request)
    {
        $request->validate([
            'terms_title'   => ['required', 'string', 'max:150'],
            'terms_content' => ['required', 'string', 'max:20000'],
        ]);

        Setting::set('terms_title',   $request->terms_title);
        Setting::set('terms_content', $request->terms_content);

        return back()->with('success', 'İstifadə qaydaları yadda saxlandı.');
    }

    public function promoSettings()
    {
        $discountPercent   = Setting::get('promo_default_discount_percent', 20);
        $commissionPercent = Setting::get('promo_default_commission_percent', 5);

        return view('admin.settings.promo', compact('discountPercent', 'commissionPercent'));
    }

    public function savePromoSettings(Request $request)
    {
        $request->validate([
            'promo_default_discount_percent'   => ['required', 'numeric', 'min:0', 'max:100'],
            'promo_default_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::set('promo_default_discount_percent',   $request->promo_default_discount_percent);
        Setting::set('promo_default_commission_percent', $request->promo_default_commission_percent);

        return back()->with('success', 'Promotor ayarları yadda saxlandı.');
    }

    public function cronLog()
    {
        $logPath = storage_path('logs/cron.log');
        $lines   = [];

        if (file_exists($logPath)) {
            $all   = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_slice(array_reverse($all), 0, 200); // last 200 lines, newest first
        }

        $smsDriver       = config('services.sms.driver', 'log');
        $reminderMinutes = (int) Setting::get('reminder_minutes_before', 120);

        $nextAppointments = \App\Models\Appointment::with('patient')
            ->where('reminder_sent', false)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        return view('admin.cron-log', compact('lines', 'logPath', 'smsDriver', 'reminderMinutes', 'nextAppointments'));
    }

    public function smtpSettings()
    {
        $settings = [
            'smtp_host'         => Setting::get('smtp_host', ''),
            'smtp_port'         => Setting::get('smtp_port', '587'),
            'smtp_encryption'   => Setting::get('smtp_encryption', 'tls'),
            'smtp_username'     => Setting::get('smtp_username', ''),
            'smtp_from_address' => Setting::get('smtp_from_address', ''),
            'smtp_from_name'    => Setting::get('smtp_from_name', ''),
        ];

        return view('admin.settings.smtp', compact('settings'));
    }

    public function saveSmtpSettings(Request $request)
    {
        $request->validate([
            'smtp_host'         => ['required', 'string', 'max:255'],
            'smtp_port'         => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption'   => ['required', 'in:tls,ssl,none'],
            'smtp_username'     => ['required', 'string', 'max:255'],
            'smtp_password'     => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['required', 'email', 'max:255'],
            'smtp_from_name'    => ['required', 'string', 'max:100'],
        ]);

        Setting::set('smtp_host',         $request->smtp_host);
        Setting::set('smtp_port',         $request->smtp_port);
        Setting::set('smtp_encryption',   $request->smtp_encryption);
        Setting::set('smtp_username',     $request->smtp_username);
        Setting::set('smtp_from_address', $request->smtp_from_address);
        Setting::set('smtp_from_name',    $request->smtp_from_name);

        // Only update password if provided
        if ($request->filled('smtp_password')) {
            Setting::set('smtp_password', encrypt($request->smtp_password));
        }

        return back()->with('success', 'SMTP ayarları yadda saxlandı.');
    }
}
