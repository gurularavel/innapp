<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\DoctorBreak;
use App\Models\DoctorWorkingHours;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $existingHours = $user->workingHours()->get()->keyBy('day_of_week');
        $existingBreaks = $user->breaks()->orderBy('day_of_week')->get();

        $workingHours = [];
        for ($day = 1; $day <= 7; $day++) {
            $workingHours[$day] = $existingHours->get($day) ?? (object)[
                'day_of_week' => $day,
                'start_time'  => '09:00',
                'end_time'    => '18:00',
                'is_working'  => true,
            ];
        }

        $specialties = \App\Models\Specialty::where('is_active', true)->orderBy('name')->get();
        $clinic      = $user->clinic;

        return view('doctor.profile.edit', compact('user', 'workingHours', 'existingBreaks', 'specialties', 'clinic'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'surname'      => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'specialty_id' => 'nullable|exists:specialties,id',
        ]);

        $user->update($validated);

        // Clinic identity is a separate, owner-only form on the same page.
        if ($user->canManageClinic() && $request->filled('clinic_name')) {
            $this->saveClinic($request, $user);
        }

        return redirect()->route('panel.profile.edit')
            ->with('success', 'Profil yeniləndi.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('panel.profile.edit')
            ->with('success', 'Şifrə uğurla dəyişdirildi.');
    }

    public function smsTemplates(WhatsAppService $whatsapp)
    {
        // WhatsApp can only be picked once the admin has configured it.
        $whatsappAvailable = $whatsapp->isConfigured();
        $clinic            = Auth::user()->clinic;

        return view('doctor.sms-templates.index', compact('whatsappAvailable', 'clinic'));
    }

    public function saveSmsTemplates(Request $request, WhatsAppService $whatsapp)
    {
        $request->validate([
            'sms_appointment_template' => ['nullable', 'string', 'max:160'],
            'sms_reminder_template'    => ['nullable', 'string', 'max:160'],
            'sms_copy_to_self'         => ['boolean'],
            'notify_channel'           => ['required', 'in:sms,whatsapp,both'],
        ]);

        $user = Auth::user();

        // Messaging identity is clinic-wide, so only the owner may change it.
        abort_unless($user->canManageClinic(), 403, 'Bildiriş ayarlarını yalnız klinika sahibi dəyişə bilər.');

        $channel = $request->notify_channel;

        // Guard against a stale form: WhatsApp may have been turned off since the page loaded.
        if ($channel !== 'sms' && !$whatsapp->isConfigured()) {
            return back()->withInput()->withErrors([
                'notify_channel' => 'WhatsApp hazırda aktiv deyil. Zəhmət olmasa administrator ilə əlaqə saxlayın.',
            ]);
        }

        $user->clinic?->update([
            'sms_appointment_template' => $request->sms_appointment_template ?: null,
            'sms_reminder_template'    => $request->sms_reminder_template ?: null,
            'sms_copy_to_self'         => $request->boolean('sms_copy_to_self'),
            'notify_channel'           => $channel,
        ]);

        return redirect()->route('panel.sms-templates.index')
            ->with('success', 'Bildiriş ayarları yadda saxlandı.');
    }

    public function workingHours()
    {
        $user = Auth::user();
        $existingHours = $user->workingHours()->get()->keyBy('day_of_week');
        $existingBreaks = $user->breaks()->orderBy('day_of_week')->get();

        $workingHours = [];
        for ($day = 1; $day <= 7; $day++) {
            $workingHours[$day] = $existingHours->get($day) ?? (object)[
                'day_of_week' => $day,
                'start_time'  => '09:00',
                'end_time'    => '18:00',
                'is_working'  => true,
            ];
        }

        $specialties = \App\Models\Specialty::where('is_active', true)->orderBy('name')->get();
        $clinic      = $user->clinic;

        return view('doctor.profile.edit', compact('user', 'workingHours', 'existingBreaks', 'specialties', 'clinic'));
    }

    /**
     * Clinic-wide identity used in messages: name, address and the short map link.
     */
    private function saveClinic(Request $request, $user): void
    {
        $data = $request->validate([
            'clinic_name'    => 'required|string|max:100',
            'clinic_address' => 'nullable|string|max:255',
            'clinic_phone'   => 'nullable|string|max:20',
            'clinic_map_url' => 'nullable|url|max:2000',
        ]);

        $clinic = $user->clinic;

        if (! $clinic) {
            return;
        }

        $mapUrl  = $data['clinic_map_url'] ?? null;
        $mapCode = $clinic->map_code;

        if ($mapUrl && ! $mapCode) {
            $mapCode = Clinic::generateMapCode();
        }

        if (! $mapUrl) {
            $mapCode = null;
        }

        $clinic->update([
            'name'     => $data['clinic_name'],
            'address'  => $data['clinic_address'] ?? null,
            'phone'    => $data['clinic_phone'] ?? null,
            'map_url'  => $mapUrl,
            'map_code' => $mapCode,
        ]);
    }

    public function saveWorkingHours(Request $request)
    {
        $request->validate([
            'working_hours'                    => 'required|array',
            'working_hours.*.start_time'       => 'required_if:working_hours.*.is_working,1|nullable|date_format:H:i',
            'working_hours.*.end_time'         => 'required_if:working_hours.*.is_working,1|nullable|date_format:H:i',
        ]);

        $doctorId = Auth::id();

        foreach ($request->working_hours as $day => $hours) {
            $isWorking = isset($hours['is_working']) && $hours['is_working'];

            DoctorWorkingHours::updateOrCreate(
                ['doctor_id' => $doctorId, 'day_of_week' => (int) $day],
                [
                    'is_working' => $isWorking,
                    'start_time' => $isWorking ? ($hours['start_time'] ?? '09:00') : '09:00',
                    'end_time'   => $isWorking ? ($hours['end_time'] ?? '18:00') : '18:00',
                ]
            );
        }

        // Delete old breaks and insert new ones
        DoctorBreak::where('doctor_id', $doctorId)->delete();

        if ($request->has('breaks') && is_array($request->breaks)) {
            foreach ($request->breaks as $brk) {
                if (!empty($brk['day_of_week']) && !empty($brk['start_time']) && !empty($brk['end_time'])) {
                    DoctorBreak::create([
                        'doctor_id'   => $doctorId,
                        'day_of_week' => (int) $brk['day_of_week'],
                        'start_time'  => $brk['start_time'],
                        'end_time'    => $brk['end_time'],
                        'label'       => $brk['label'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('panel.profile.working-hours')
            ->with('success', 'İş saatları yadda saxlandı.');
    }
}
