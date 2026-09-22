<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitFile;
use App\Models\PatientVisitTooth;
use App\Models\TreatmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Support\PatientFiles;
use Illuminate\Validation\Rule;

class PatientVisitController extends Controller
{
    public function create(Patient $patient)
    {
        $this->authorizePatient($patient);
        $treatmentTypes = Auth::user()->treatmentTypes()->orderBy('name')->get();
        $dentalChart    = (bool) Auth::user()->clinic?->dental_chart_enabled;
        $visitTeeth     = [];
        $toothHistory   = $dentalChart ? $this->toothHistory($patient) : [];

        return view('doctor.patients.visits.form', compact(
            'patient', 'treatmentTypes', 'dentalChart', 'visitTeeth', 'toothHistory'
        ));
    }

    public function store(Request $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $validated = $request->validate([
            'visited_at' => 'required|date',
            'title'      => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
            'files.*'    => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|extensions:jpg,jpeg,png,gif,webp,pdf|max:5120',
            'teeth'          => 'nullable|array',
            'teeth.*.status' => ['required', Rule::in(array_keys(PatientVisitTooth::STATUSES))],
            'teeth.*.note'   => 'nullable|string|max:255',
        ]);

        $visit = PatientVisit::create([
            'patient_id' => $patient->id,
            'clinic_id'  => Auth::user()->clinic_id,
            'doctor_id'  => Auth::id(),
            'visited_at' => $validated['visited_at'],
            'title'      => $validated['title'] ?? null,
            'notes'      => $validated['notes'] ?? null,
        ]);

        if (Auth::user()->clinic?->dental_chart_enabled) {
            $this->syncTeeth($visit, $request->input('teeth'));
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = PatientFiles::store($file, PatientFiles::VISITS);
                PatientVisitFile::create([
                    'patient_visit_id' => $visit->id,
                    'file_path'        => $path,
                    'original_name'    => $file->getClientOriginalName(),
                    'mime_type'        => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('panel.patients.show', $patient)
            ->with('success', 'Ziyarət qeydə alındı.');
    }

    public function edit(Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatient($patient);
        $this->authorizeVisit($patient, $visit);
        $visit->load(['files', 'teeth']);
        $treatmentTypes = Auth::user()->treatmentTypes()->orderBy('name')->get();
        $dentalChart    = (bool) Auth::user()->clinic?->dental_chart_enabled;
        $visitTeeth     = $visit->teeth->map(fn ($tooth) => [
            'tooth_number' => $tooth->tooth_number,
            'status'       => $tooth->status,
            'note'         => $tooth->note,
        ])->all();
        $toothHistory   = $dentalChart ? $this->toothHistory($patient, $visit->id) : [];

        return view('doctor.patients.visits.form', compact(
            'patient', 'visit', 'treatmentTypes', 'dentalChart', 'visitTeeth', 'toothHistory'
        ));
    }

    public function update(Request $request, Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatient($patient);
        $this->authorizeVisit($patient, $visit);

        $validated = $request->validate([
            'visited_at' => 'required|date',
            'title'      => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
            'files.*'    => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|extensions:jpg,jpeg,png,gif,webp,pdf|max:5120',
            'teeth'          => 'nullable|array',
            'teeth.*.status' => ['required', Rule::in(array_keys(PatientVisitTooth::STATUSES))],
            'teeth.*.note'   => 'nullable|string|max:255',
        ]);

        $visit->update([
            'visited_at' => $validated['visited_at'],
            'title'      => $validated['title'] ?? null,
            'notes'      => $validated['notes'] ?? null,
        ]);

        if (Auth::user()->clinic?->dental_chart_enabled) {
            $this->syncTeeth($visit, $request->input('teeth'));
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = PatientFiles::store($file, PatientFiles::VISITS);
                PatientVisitFile::create([
                    'patient_visit_id' => $visit->id,
                    'file_path'        => $path,
                    'original_name'    => $file->getClientOriginalName(),
                    'mime_type'        => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('panel.patients.show', $patient)
            ->with('success', 'Ziyarət yeniləndi.');
    }

    public function destroy(Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatient($patient);
        $this->authorizeVisit($patient, $visit);

        foreach ($visit->files as $file) {
            PatientFiles::delete($file->file_path);
        }
        $visit->delete();

        return redirect()->route('panel.patients.show', $patient)
            ->with('success', 'Ziyarət silindi.');
    }

    public function destroyFile(Patient $patient, PatientVisitFile $file)
    {
        $this->authorizePatient($patient);

        if ($file->visit->patient_id !== $patient->id) {
            abort(403);
        }

        PatientFiles::delete($file->file_path);
        $file->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Replace the visit's tooth marks with whatever the chart posted.
     *
     * The FDI keys arrive from the browser, so anything the chart itself
     * cannot draw is dropped instead of trusted.
     */
    private function syncTeeth(PatientVisit $visit, $teeth): void
    {
        $visit->teeth()->delete();

        if (! is_array($teeth)) {
            return;
        }

        $rows = [];

        foreach ($teeth as $number => $mark) {
            $number = (int) $number;

            if (! PatientVisitTooth::isValidNumber($number) || isset($rows[$number]) || ! is_array($mark)) {
                continue;
            }

            $rows[$number] = [
                'patient_visit_id' => $visit->id,
                'tooth_number'     => $number,
                'status'           => $mark['status'] ?? PatientVisitTooth::DEFAULT_STATUS,
                'note'             => ($mark['note'] ?? null) ?: null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if ($rows) {
            PatientVisitTooth::insert(array_values($rows));
        }
    }

    /**
     * What earlier visits already recorded per tooth, newest first — the chart
     * draws these as small dots so the doctor sees the history in place.
     *
     * @return array<int, array<int, array{date: string, status: string, note: ?string}>>
     */
    private function toothHistory(Patient $patient, ?int $exceptVisitId = null): array
    {
        $rows = PatientVisitTooth::query()
            ->join('patient_visits', 'patient_visits.id', '=', 'patient_visit_teeth.patient_visit_id')
            ->where('patient_visits.patient_id', $patient->id)
            ->when($exceptVisitId, fn ($q) => $q->where('patient_visits.id', '!=', $exceptVisitId))
            ->orderByDesc('patient_visits.visited_at')
            ->get([
                'patient_visit_teeth.tooth_number',
                'patient_visit_teeth.status',
                'patient_visit_teeth.note',
                'patient_visits.visited_at',
            ]);

        $history = [];

        foreach ($rows as $row) {
            $history[$row->tooth_number][] = [
                'date'   => Carbon::parse($row->visited_at)->format('d.m.Y'),
                'status' => $row->status,
                'note'   => $row->note,
            ];
        }

        return $history;
    }

    private function authorizePatient(Patient $patient): void
    {
        if ($patient->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }
    }

    private function authorizeVisit(Patient $patient, PatientVisit $visit): void
    {
        if ($visit->patient_id !== $patient->id) {
            abort(403);
        }
    }
}
