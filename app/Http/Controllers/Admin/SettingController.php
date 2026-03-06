<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
