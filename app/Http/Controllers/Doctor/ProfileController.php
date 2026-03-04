<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorBreak;
use App\Models\DoctorWorkingHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        return view('doctor.profile.edit', compact('user', 'workingHours', 'existingBreaks'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'surname'      => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'muessise_adi' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

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

    public function saveSmsTemplates(Request $request)
    {
        $request->validate([
            'sms_appointment_template' => ['nullable', 'string', 'max:160'],
            'sms_reminder_template'    => ['nullable', 'string', 'max:160'],
        ]);

        Auth::user()->update([
            'sms_appointment_template' => $request->sms_appointment_template ?: null,
            'sms_reminder_template'    => $request->sms_reminder_template ?: null,
        ]);

        return redirect()->route('panel.profile.edit')
            ->with('success', 'SMS şablonları yadda saxlandı.');
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

        return view('doctor.profile.edit', compact('user', 'workingHours', 'existingBreaks'));
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
