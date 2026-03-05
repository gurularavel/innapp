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
        $reminderHoursBefore  = (int) Setting::get('reminder_hours_before', 2);

        return view('admin.settings.sms-templates', compact(
            'appointmentTemplate',
            'reminderTemplate',
            'defaultMuessise',
            'reminderHoursBefore'
        ));
    }

    public function saveSmsTemplates(Request $request)
    {
        $request->validate([
            'sms_appointment_template' => ['required', 'string', 'max:160'],
            'sms_reminder_template'    => ['required', 'string', 'max:160'],
            'default_muessise_adi'     => ['required', 'string', 'max:100'],
            'reminder_hours_before'    => ['required', 'integer', 'min:1', 'max:48'],
        ]);

        Setting::set('sms_appointment_template', $request->sms_appointment_template);
        Setting::set('sms_reminder_template',    $request->sms_reminder_template);
        Setting::set('default_muessise_adi',     $request->default_muessise_adi);
        Setting::set('reminder_hours_before',    $request->reminder_hours_before);

        return back()->with('success', 'SMS şablonları yadda saxlandı.');
    }
}
