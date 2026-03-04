<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorBreak;
use App\Models\DoctorWorkingHours;
use App\Models\Patient;
use App\Models\TreatmentType;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct(private SmsService $smsService) {}

    public function index(Request $request)
    {
        $query = Auth::user()->appointments()->with('patient', 'treatmentType');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $appointments = $query->orderBy('scheduled_at', 'desc')->paginate(15);
        $patients = Auth::user()->patients()->get();

        return view('doctor.appointments.index', compact('appointments', 'patients'));
    }

    public function create()
    {
        $patients = Auth::user()->patients()->get();
        $treatmentTypes = Auth::user()->treatmentTypes()->get();
        return view('doctor.appointments.create', compact('patients', 'treatmentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'treatment_type_id' => 'nullable|exists:treatment_types,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        $validated['duration_minutes'] = (int) $validated['duration_minutes'];

        $patient = Patient::findOrFail($validated['patient_id']);
        if ($patient->doctor_id !== Auth::id()) {
            abort(403);
        }

        $startTime = Carbon::parse($validated['scheduled_at']);
        $endTime   = (clone $startTime)->addMinutes($validated['duration_minutes']);

        $conflictCount = Appointment::where('doctor_id', Auth::id())
            ->whereNotIn('status', ['cancelled'])
            ->where('scheduled_at', '<', $endTime)
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$startTime])
            ->count();

        if ($conflictCount > 0) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Bu vaxtda başqa randevu var. Zəhmət olmasa başqa vaxt seçin.']);
        }

        $validated['doctor_id'] = Auth::id();

        $appointment = Appointment::create($validated);

        // Send appointment confirmation SMS
        if (in_array($appointment->status, ['pending', 'confirmed'])) {
            $this->smsService->sendAppointmentSms($appointment);
        }

        return redirect()->route('panel.appointments.index')
            ->with('success', 'Randevu uğurla yaradıldı.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        $appointment->load('patient', 'treatmentType', 'smsLogs');
        return view('doctor.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        $patients = Auth::user()->patients()->get();
        $treatmentTypes = Auth::user()->treatmentTypes()->get();
        return view('doctor.appointments.edit', compact('appointment', 'patients', 'treatmentTypes'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'treatment_type_id' => 'nullable|exists:treatment_types,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        $validated['duration_minutes'] = (int) $validated['duration_minutes'];

        $startTime = Carbon::parse($validated['scheduled_at']);
        $endTime   = (clone $startTime)->addMinutes($validated['duration_minutes']);

        $conflictCount = Appointment::where('doctor_id', Auth::id())
            ->whereNotIn('status', ['cancelled'])
            ->where('id', '!=', $appointment->id)
            ->where('scheduled_at', '<', $endTime)
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$startTime])
            ->count();

        if ($conflictCount > 0) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Bu vaxtda başqa randevu var. Zəhmət olmasa başqa vaxt seçin.']);
        }

        $appointment->update($validated);

        return redirect()->route('panel.appointments.index')
            ->with('success', 'Randevu yeniləndi.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        $appointment->delete();

        return redirect()->route('panel.appointments.index')
            ->with('success', 'Randevu silindi.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'status' => $appointment->status_label,
        ]);
    }

    public function availableSlots(Request $request)
    {
        $date      = $request->get('date');
        $duration  = (int) $request->get('duration', 30);
        $excludeId = $request->get('exclude_id');

        if (!$date) {
            return response()->json(['error' => 'date required'], 422);
        }

        $doctorId = Auth::id();
        $carbon   = Carbon::parse($date);
        // Carbon: 0=Sun,1=Mon..6=Sat → our DB: 1=Mon..7=Sun
        $dayOfWeek = $carbon->dayOfWeek === 0 ? 7 : $carbon->dayOfWeek;

        // Working hours
        $wh = DoctorWorkingHours::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if ($wh && !$wh->is_working) {
            return response()->json(['working' => false, 'working_hours' => null, 'conflicts' => [], 'available_slots' => []]);
        }

        $startH = $wh ? substr($wh->start_time, 0, 5) : '08:00';
        $endH   = $wh ? substr($wh->end_time, 0, 5) : '20:00';

        // Existing appointments
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->whereDate('scheduled_at', $date)
            ->get();

        // Breaks
        $breaks = DoctorBreak::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        // Generate slots
        $slotStart = Carbon::parse("{$date} {$startH}");
        $slotEnd   = Carbon::parse("{$date} {$endH}");
        $available = [];

        $current = clone $slotStart;
        while ((clone $current)->addMinutes($duration)->lte($slotEnd)) {
            $slotS = clone $current;
            $slotE = (clone $current)->addMinutes($duration);

            $conflict = false;

            // Check appointments
            foreach ($appointments as $apt) {
                $aptStart = Carbon::parse($apt->scheduled_at);
                $aptEnd   = (clone $aptStart)->addMinutes($apt->duration_minutes);
                if ($slotS->lt($aptEnd) && $slotE->gt($aptStart)) {
                    $conflict = true;
                    break;
                }
            }

            // Check breaks
            if (!$conflict) {
                foreach ($breaks as $brk) {
                    $brkStart = Carbon::parse("{$date} {$brk->start_time}");
                    $brkEnd   = Carbon::parse("{$date} {$brk->end_time}");
                    if ($slotS->lt($brkEnd) && $slotE->gt($brkStart)) {
                        $conflict = true;
                        break;
                    }
                }
            }

            if (!$conflict) {
                $available[] = $slotS->format('H:i');
            }

            $current->addMinutes(30);
        }

        // Conflict check for currently selected time (if any)
        $conflicts = [];
        $scheduledAt = $request->get('scheduled_at');
        if ($scheduledAt) {
            $selStart = Carbon::parse($scheduledAt);
            $selEnd   = (clone $selStart)->addMinutes($duration);
            $conflicting = Appointment::where('doctor_id', $doctorId)
                ->whereNotIn('status', ['cancelled'])
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->where('scheduled_at', '<', $selEnd)
                ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$selStart])
                ->with('patient')
                ->get();
            foreach ($conflicting as $c) {
                $conflicts[] = [
                    'id'           => $c->id,
                    'patient_name' => $c->patient->full_name,
                    'start'        => Carbon::parse($c->scheduled_at)->format('H:i'),
                    'end'          => Carbon::parse($c->scheduled_at)->addMinutes($c->duration_minutes)->format('H:i'),
                ];
            }
        }

        return response()->json([
            'working'         => true,
            'working_hours'   => ['start' => $startH, 'end' => $endH],
            'conflicts'       => $conflicts,
            'available_slots' => $available,
        ]);
    }

    private function authorizeAppointment(Appointment $appointment): void
    {
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403);
        }
    }
}
