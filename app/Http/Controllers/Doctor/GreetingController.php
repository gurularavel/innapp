<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\ValidatesHolidays;
use App\Http\Controllers\Controller;
use App\Models\ClinicHolidaySetting;
use App\Models\Holiday;
use App\Models\Setting;
use App\Services\GreetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Birthday and holiday greetings for one clinic.
 *
 * Greetings are off until the clinic turns them on. The platform-wide holiday
 * list comes from the admin and can be switched off or reworded per clinic; on
 * top of it a clinic may keep its own dates, which nobody else sees.
 */
class GreetingController extends Controller
{
    use ValidatesHolidays;

    public function index(GreetingService $greetings)
    {
        $clinic = Auth::user()->clinic;

        $sharedHolidays = Holiday::shared()->active()->inCalendarOrder()->get();
        $overrides      = $clinic->holidaySettings()->get()->keyBy('holiday_id');
        $ownHolidays    = $clinic->holidays()->inCalendarOrder()->get();

        $defaults = [
            'birthday' => (string) Setting::get('sms_birthday_template', ''),
            'holiday'  => (string) Setting::get('sms_holiday_template', ''),
        ];

        $sendHour = $greetings->sendHour();
        $canEdit  = Auth::user()->canManageClinic();

        return view('doctor.greetings.index', compact(
            'clinic', 'sharedHolidays', 'overrides', 'ownHolidays', 'defaults', 'sendHour', 'canEdit'
        ));
    }

    public function save(Request $request)
    {
        $clinic = $this->editableClinic();

        $request->validate([
            'birthday_greetings_enabled' => ['boolean'],
            'holiday_greetings_enabled'  => ['boolean'],
            'sms_birthday_template'      => ['nullable', 'string', 'max:160'],
            'holidays'                   => ['array'],
            'holidays.*.enabled'         => ['nullable', 'boolean'],
            'holidays.*.template'        => ['nullable', 'string', 'max:160'],
        ]);

        $clinic->update([
            'birthday_greetings_enabled' => $request->boolean('birthday_greetings_enabled'),
            'holiday_greetings_enabled'  => $request->boolean('holiday_greetings_enabled'),
            'sms_birthday_template'      => trim((string) $request->sms_birthday_template) ?: null,
        ]);

        $this->saveHolidayOverrides($clinic, (array) $request->input('holidays', []));

        return redirect()->route('panel.greetings.index')
            ->with('success', 'Təbrik ayarları yadda saxlandı.');
    }

    // -------------------------------------------------------------------------
    // The clinic's own dates
    // -------------------------------------------------------------------------

    public function createHoliday()
    {
        $this->editableClinic();

        $holiday = new Holiday(['month' => now()->month, 'day' => now()->day, 'is_active' => true]);

        return view('doctor.greetings.holiday-create', compact('holiday'));
    }

    public function storeHoliday(Request $request)
    {
        $clinic = $this->editableClinic();

        $clinic->holidays()->create($this->validatedHoliday($request));

        return redirect()->route('panel.greetings.index')
            ->with('success', 'Tarix əlavə edildi.');
    }

    public function editHoliday(Holiday $holiday)
    {
        $this->authorizeHoliday($holiday);

        return view('doctor.greetings.holiday-edit', compact('holiday'));
    }

    public function updateHoliday(Request $request, Holiday $holiday)
    {
        $this->authorizeHoliday($holiday);

        $holiday->update($this->validatedHoliday($request));

        return redirect()->route('panel.greetings.index')
            ->with('success', 'Tarix yeniləndi.');
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $this->authorizeHoliday($holiday);

        $holiday->delete();

        return redirect()->route('panel.greetings.index')
            ->with('success', 'Tarix silindi.');
    }

    // -------------------------------------------------------------------------

    /**
     * Write the clinic's stance on every shared holiday.
     *
     * The form always posts the full list, so an absent entry genuinely means
     * "switched off" rather than "not submitted".
     *
     * @param  array<int, array{enabled?: mixed, template?: string|null}>  $submitted
     */
    private function saveHolidayOverrides($clinic, array $submitted): void
    {
        $sharedIds = Holiday::shared()->active()->pluck('id');

        foreach ($sharedIds as $holidayId) {
            $row = $submitted[$holidayId] ?? [];

            ClinicHolidaySetting::updateOrCreate(
                ['clinic_id' => $clinic->id, 'holiday_id' => $holidayId],
                [
                    'is_enabled' => (bool) ($row['enabled'] ?? false),
                    'template'   => trim((string) ($row['template'] ?? '')) ?: null,
                ]
            );
        }
    }

    /** Messaging is clinic-wide, so only the owner may change any of it. */
    private function editableClinic()
    {
        $user = Auth::user();

        abort_unless($user->canManageClinic(), 403, 'Təbrik ayarlarını yalnız klinika sahibi dəyişə bilər.');

        return $user->clinic;
    }

    /** A clinic may only touch its own dates — never the platform-wide list. */
    private function authorizeHoliday(Holiday $holiday): void
    {
        $clinic = $this->editableClinic();

        abort_unless($holiday->clinic_id === $clinic->id, 404);
    }
}
