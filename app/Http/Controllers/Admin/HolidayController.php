<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ValidatesHolidays;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

/**
 * The platform-wide holiday calendar.
 *
 * Every clinic sees this list and may switch an entry off or reword it; the
 * admin owns the dates themselves. A clinic's own dates are managed from the
 * clinic panel and are never touched here.
 */
class HolidayController extends Controller
{
    use ValidatesHolidays;

    public function index()
    {
        $holidays = Holiday::shared()
            ->withCount(['clinicSettings as disabled_count' => fn ($q) => $q->where('is_enabled', false)])
            ->inCalendarOrder()
            ->get();

        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        $holiday = new Holiday(['month' => now()->month, 'day' => now()->day, 'is_active' => true]);

        return view('admin.holidays.create', compact('holiday'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedHoliday($request);

        Holiday::create($data + ['clinic_id' => null]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Bayram əlavə edildi.');
    }

    public function edit(Holiday $holiday)
    {
        $this->authorizeShared($holiday);

        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $this->authorizeShared($holiday);

        $holiday->update($this->validatedHoliday($request));

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Bayram yeniləndi.');
    }

    public function destroy(Holiday $holiday)
    {
        $this->authorizeShared($holiday);

        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Bayram silindi.');
    }

    // -------------------------------------------------------------------------

    /** The clinic panel owns clinic-specific dates; this screen must not reach them. */
    private function authorizeShared(Holiday $holiday): void
    {
        abort_unless($holiday->isShared(), 404);
    }
}
