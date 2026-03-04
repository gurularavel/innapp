<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorWorkingHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return view('doctor.calendar.index');
    }

    public function events(Request $request)
    {
        $query = Auth::user()->appointments()
            ->with('patient', 'treatmentType');

        if ($request->filled('start')) {
            $query->where('scheduled_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->where('scheduled_at', '<=', $request->end);
        }

        $appointments = $query->get();

        $events = $appointments->map(function (Appointment $appointment) {
            $color = $appointment->treatmentType?->color ?? '#3788d8';

            return [
                'id' => $appointment->id,
                'title' => $appointment->patient->full_name
                    . ($appointment->treatmentType ? ' - ' . $appointment->treatmentType->name : ''),
                'start' => $appointment->scheduled_at->toIso8601String(),
                'end' => $appointment->end_time->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'status' => $appointment->status,
                    'status_label' => $appointment->status_label,
                    'status_badge' => $appointment->status_badge,
                    'patient_id' => $appointment->patient_id,
                    'patient_name' => $appointment->patient->full_name,
                    'treatment_type' => $appointment->treatmentType?->name ?? 'Xidmət növü yoxdur',
                    'notes' => $appointment->notes,
                    'appointment_url' => route('panel.appointments.show', $appointment->id),
                ],
            ];
        });

        // Build businessHours from doctor's working hours
        $workingHours = DoctorWorkingHours::where('doctor_id', Auth::id())
            ->where('is_working', true)
            ->get();

        // FullCalendar uses 0=Sunday, 1=Monday ... 6=Saturday
        // Our DB: 1=Monday ... 7=Sunday
        $fcDayMap = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 0];

        $businessHours = $workingHours->map(function (DoctorWorkingHours $wh) use ($fcDayMap) {
            return [
                'daysOfWeek' => [$fcDayMap[$wh->day_of_week]],
                'startTime'  => substr($wh->start_time, 0, 5),
                'endTime'    => substr($wh->end_time, 0, 5),
            ];
        })->values();

        return response()->json([
            'events'        => $events,
            'businessHours' => $businessHours,
        ]);
    }
}
