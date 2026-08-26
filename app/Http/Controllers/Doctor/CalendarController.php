<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorBreak;
use App\Models\DoctorWorkingHours;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $treatmentTypes = $user->treatmentTypes()->get(['id', 'name', 'duration_minutes', 'color']);
        $staff          = $this->selectableStaff();

        // Receptionists have no diary of their own, so they start on the whole clinic.
        $defaultStaffId = $user->takesAppointments() ? $user->id : null;

        return view('doctor.calendar.index', compact('treatmentTypes', 'staff', 'defaultStaffId'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();

        $staffId = $this->resolveStaffId($request);

        $query = $staffId
            ? $user->clinicAppointments()->where('doctor_id', $staffId)
            : $user->clinicAppointments();

        $query->with('patient', 'treatmentType', 'doctor');

        if ($request->filled('start')) {
            $query->where('scheduled_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->where('scheduled_at', '<=', $request->end);
        }

        $appointments = $query->get();

        $events = $appointments->map(function (Appointment $appointment) use ($staffId) {
            $color = $appointment->treatmentType?->color ?? '#3788d8';

            // On the clinic-wide view the staff member matters more than the service.
            $title = $appointment->patient->full_name
                . ($appointment->treatmentType ? ' - ' . $appointment->treatmentType->name : '');

            if (! $staffId && $appointment->doctor) {
                $title = $appointment->doctor->name . ': ' . $title;
            }

            return [
                'id' => $appointment->id,
                'title' => $title,
                'start' => $appointment->scheduled_at->toIso8601String(),
                'end' => $appointment->end_time->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'status' => $appointment->status,
                    'status_label' => $appointment->status_label,
                    'status_badge' => $appointment->status_badge,
                    'patient_id' => $appointment->patient_id,
                    'patient_name' => $appointment->patient->full_name,
                    'staff_id' => $appointment->doctor_id,
                    'staff_name' => $appointment->doctor?->full_name ?? '—',
                    'treatment_type' => $appointment->treatmentType?->name ?? 'Xidmət növü yoxdur',
                    'notes' => $appointment->notes,
                    'appointment_url' => route('panel.appointments.show', $appointment->id),
                ],
            ];
        });

        // FullCalendar uses 0=Sunday, 1=Monday ... 6=Saturday
        // Our DB: 1=Monday ... 7=Sunday
        $fcDayMap = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 0];

        // Business hours and breaks only make sense for one person at a time.
        $businessHours = collect();
        $breakEvents   = collect();

        if ($staffId) {
            $businessHours = DoctorWorkingHours::where('doctor_id', $staffId)
                ->where('is_working', true)
                ->get()
                ->map(fn (DoctorWorkingHours $wh) => [
                    'daysOfWeek' => [$fcDayMap[$wh->day_of_week]],
                    'startTime'  => substr($wh->start_time, 0, 5),
                    'endTime'    => substr($wh->end_time, 0, 5),
                ])->values();

            $breakEvents = DoctorBreak::where('doctor_id', $staffId)
                ->get()
                ->map(fn (DoctorBreak $brk) => [
                    'startTime'  => substr($brk->start_time, 0, 5),
                    'endTime'    => substr($brk->end_time, 0, 5),
                    'daysOfWeek' => [$fcDayMap[$brk->day_of_week]],
                    'display'    => 'background',
                    'color'      => '#ffc107',
                    'title'      => $brk->label ?? 'Fasilə',
                ])->values();
        }

        return response()->json([
            'events'        => $events->merge($breakEvents)->values(),
            'businessHours' => $businessHours,
        ]);
    }

    // -------------------------------------------------------------------------

    private function seesWholeClinic(): bool
    {
        $user = Auth::user();

        return $user->isOwner() || $user->isReceptionist();
    }

    /** Staff the current user is allowed to switch between. */
    private function selectableStaff()
    {
        $user = Auth::user();

        if (! $this->seesWholeClinic()) {
            return collect($user->takesAppointments() ? [$user] : []);
        }

        return $user->clinic
            ? $user->clinic->specialists()->orderBy('name')->get()
            : collect();
    }

    /**
     * Null means "everyone in the clinic". A specialist is always pinned to
     * their own calendar regardless of what the request asks for.
     */
    private function resolveStaffId(Request $request): ?int
    {
        $user = Auth::user();

        if (! $this->seesWholeClinic()) {
            return $user->id;
        }

        if (! $request->filled('staff_id') || $request->staff_id === 'all') {
            return null;
        }

        $isMember = User::where('id', $request->staff_id)
            ->where('clinic_id', $user->clinic_id)
            ->exists();

        return $isMember ? (int) $request->staff_id : null;
    }
}
