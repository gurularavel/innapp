<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientVisitController extends Controller
{
    public function create(Patient $patient)
    {
        $this->authorizePatient($patient);
        return view('doctor.patients.visits.form', compact('patient'));
    }

    public function store(Request $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $validated = $request->validate([
            'visited_at' => 'required|date',
            'title'      => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
            'files.*'    => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ]);

        $visit = PatientVisit::create([
            'patient_id' => $patient->id,
            'doctor_id'  => Auth::id(),
            'visited_at' => $validated['visited_at'],
            'title'      => $validated['title'] ?? null,
            'notes'      => $validated['notes'] ?? null,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('patients/visits', 'public');
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
        $visit->load('files');
        return view('doctor.patients.visits.form', compact('patient', 'visit'));
    }

    public function update(Request $request, Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatient($patient);
        $this->authorizeVisit($patient, $visit);

        $validated = $request->validate([
            'visited_at' => 'required|date',
            'title'      => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
            'files.*'    => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ]);

        $visit->update([
            'visited_at' => $validated['visited_at'],
            'title'      => $validated['title'] ?? null,
            'notes'      => $validated['notes'] ?? null,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('patients/visits', 'public');
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
            Storage::disk('public')->delete($file->file_path);
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

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizePatient(Patient $patient): void
    {
        if ($patient->doctor_id !== Auth::id()) {
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
