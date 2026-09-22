<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientFieldValue;
use App\Models\SpecialtyField;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Support\PatientFiles;

class PatientController extends Controller
{
    /**
     * Custom-field uploads are documents and scans only. Both the sniffed type
     * and the extension are checked, so a script renamed to .pdf or a real
     * .html file is rejected — the private disk is the second line of defence.
     */
    public const CUSTOM_FILE_RULES = [
        'nullable', 'file', 'max:10240',
        'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
        'extensions:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
    ];

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Auth::user()->patients()
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('surname', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'surname', 'phone', 'birth_date'])
        );
    }

    public function index(Request $request)
    {
        $query = Auth::user()->patients();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15);

        return view('doctor.patients.index', compact('patients'));
    }

    public function create()
    {
        $doctor = Auth::user();
        $subscription = $doctor->activeSubscription()->with('package')->first();

        if ($subscription && $subscription->patientLimitReached()) {
            return redirect()->route('panel.patients.index')
                ->with('error', 'Müştəri limitinə çatdınız. Paketi yeniləyin.');
        }

        $fields = $this->getSpecialtyFields($doctor);
        return view('doctor.patients.create', compact('fields'));
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
        $subscription = $doctor->activeSubscription()->with('package')->first();

        if ($subscription && $subscription->patientLimitReached()) {
            return redirect()->route('panel.patients.index')
                ->with('error', 'Müştəri limitinə çatdınız. Paketi yeniləyin.');
        }

        $fields = $this->getSpecialtyFields($doctor);
        $activeKeys = $fields->where('is_active', true)->where('is_core', true)->pluck('field_key');

        $rules = [
            'name'    => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
        ];

        if ($activeKeys->contains('birth_date'))     $rules['birth_date']     = 'nullable|date';
        if ($activeKeys->contains('gender'))         $rules['gender']         = 'nullable|in:male,female,other';
        if ($activeKeys->contains('weight'))         $rules['weight']         = 'nullable|numeric|min:0|max:999';
        if ($activeKeys->contains('blood_type'))     $rules['blood_type']     = 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-';
        if ($activeKeys->contains('marital_status')) $rules['marital_status'] = 'nullable|in:single,married,divorced,widowed';
        if ($activeKeys->contains('notes'))          $rules['notes']          = 'nullable|string';
        if ($activeKeys->contains('photo'))          $rules['photo']          = 'nullable|image|max:2048';

        foreach ($fields->where('is_core', false)->where('is_active', true)->where('type', 'file') as $cf) {
            if ($cf->id) {
                $rules["custom_file_{$cf->id}"] = self::CUSTOM_FILE_RULES;
            }
        }

        $validated = $request->validate($rules);
        // Patients belong to the clinic; doctor_id records who registered them.
        $validated['clinic_id'] = $doctor->clinic_id;
        $validated['doctor_id'] = $doctor->id;

        $existing = $doctor->patients()->where('phone', $validated['phone'])->first();
        if ($existing) {
            $appointmentUrl = route('panel.appointments.create', ['patient_id' => $existing->id]);
            return redirect()->back()
                ->withInput()
                ->with('duplicate_patient', [
                    'id'   => $existing->id,
                    'name' => $existing->name . ' ' . $existing->surname,
                    'phone'=> $existing->phone,
                    'url'  => $appointmentUrl,
                ]);
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = PatientFiles::store($request->file('photo'), PatientFiles::PHOTOS);
        }

        $patient = Patient::create($validated);

        if ($subscription) {
            $subscription->increment('patients_used');
        }

        $this->saveCustomFieldValues($request, $patient, $fields);

        return redirect()->route('panel.patients.index')
            ->with('success', 'Müştəri uğurla qeydə alındı.');
    }

    public function show(Patient $patient)
    {
        $this->authorizePatient($patient);
        $patient->load(['appointments.treatmentType', 'visits.files', 'visits.teeth']);
        $customValues = $patient->fieldValues()->with('specialtyField')->get()->keyBy('specialty_field_id');
        $fields = $this->getSpecialtyFields($patient->doctor);

        $dentalChart = (bool) Auth::user()->clinic?->dental_chart_enabled;
        [$toothLatest, $toothHistory] = $dentalChart
            ? $this->buildToothChart($patient)
            : [[], []];

        return view('doctor.patients.show', compact(
            'patient', 'fields', 'customValues', 'dentalChart', 'toothLatest', 'toothHistory'
        ));
    }

    public function edit(Patient $patient)
    {
        $this->authorizePatient($patient);
        $fields = $this->getSpecialtyFields(Auth::user());
        $customValues = $patient->fieldValues()->get()->keyBy('specialty_field_id');
        return view('doctor.patients.edit', compact('patient', 'fields', 'customValues'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $doctor = Auth::user();
        $fields = $this->getSpecialtyFields($doctor);
        $activeKeys = $fields->where('is_active', true)->where('is_core', true)->pluck('field_key');

        $rules = [
            'name'         => 'required|string|max:255',
            'surname'      => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'remove_photo' => 'nullable|boolean',
        ];

        if ($activeKeys->contains('birth_date'))     $rules['birth_date']     = 'nullable|date';
        if ($activeKeys->contains('gender'))         $rules['gender']         = 'nullable|in:male,female,other';
        if ($activeKeys->contains('weight'))         $rules['weight']         = 'nullable|numeric|min:0|max:999';
        if ($activeKeys->contains('blood_type'))     $rules['blood_type']     = 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-';
        if ($activeKeys->contains('marital_status')) $rules['marital_status'] = 'nullable|in:single,married,divorced,widowed';
        if ($activeKeys->contains('notes'))          $rules['notes']          = 'nullable|string';
        if ($activeKeys->contains('photo'))          $rules['photo']          = 'nullable|image|max:2048';

        foreach ($fields->where('is_core', false)->where('is_active', true)->where('type', 'file') as $cf) {
            if ($cf->id) {
                $rules["custom_file_{$cf->id}"] = self::CUSTOM_FILE_RULES;
            }
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('photo')) {
            PatientFiles::delete($patient->photo);
            $validated['photo'] = PatientFiles::store($request->file('photo'), PatientFiles::PHOTOS);
        } elseif ($request->boolean('remove_photo') && $patient->photo) {
            PatientFiles::delete($patient->photo);
            $validated['photo'] = null;
        }

        $patient->update($validated);

        $this->saveCustomFieldValues($request, $patient, $fields);

        return redirect()->route('panel.patients.index')
            ->with('success', 'Müştəri məlumatları yeniləndi.');
    }

    public function destroy(Patient $patient)
    {
        $this->authorizePatient($patient);

        // The rows cascade in the database; the files on disk do not.
        PatientFiles::delete($patient->photo);
        foreach ($patient->fieldValues()->whereNotNull('value')->get() as $value) {
            if ($value->specialtyField?->type === 'file') {
                PatientFiles::delete($value->value);
            }
        }
        foreach ($patient->visits()->with('files')->get() as $visit) {
            foreach ($visit->files as $file) {
                PatientFiles::delete($file->file_path);
            }
        }

        $patient->delete();

        // Qeyd: patients_used QƏSDƏN azaldılmır. Limit "dövr ərzində cəmi əlavə"
        // modelidir — müştəri silmək kvotada yer açmır (yalnız paket yeniləmə/yüksəltmə).

        return redirect()->route('panel.patients.index')
            ->with('success', 'Müştəri silindi.');
    }

    /**
     * The patient's cumulative dental chart: each tooth's most recent mark plus
     * the older ones behind it. Visits are already ordered newest first.
     *
     * @return array{0: array<int, array>, 1: array<int, array>}
     */
    private function buildToothChart(Patient $patient): array
    {
        $byTooth = [];

        foreach ($patient->visits as $visit) {
            foreach ($visit->teeth as $tooth) {
                $byTooth[$tooth->tooth_number][] = [
                    'date'   => $visit->visited_at->format('d.m.Y'),
                    'status' => $tooth->status,
                    'note'   => $tooth->note,
                ];
            }
        }

        $latest  = [];
        $history = [];

        foreach ($byTooth as $number => $entries) {
            $latest[] = [
                'tooth_number' => $number,
                'status'       => $entries[0]['status'],
                'note'         => $entries[0]['note'],
            ];
            $older = array_slice($entries, 1);
            if ($older) {
                $history[$number] = $older;
            }
        }

        return [$latest, $history];
    }

    private function getSpecialtyFields($doctor): Collection
    {
        $specialty = $doctor->specialty ?? null;

        if (!$specialty) {
            // No specialty → virtual collection with all core fields active
            return collect(SpecialtyField::coreFields())->map(function ($def, $key) {
                $sf = new SpecialtyField([
                    'field_key'  => $key,
                    'label'      => $def['label'],
                    'type'       => $def['type'],
                    'options'    => $def['options'],
                    'is_active'  => true,
                    'is_core'    => true,
                    'sort_order' => $def['sort_order'],
                ]);
                $sf->id = null;
                return $sf;
            })->sortBy('sort_order')->values();
        }

        return $specialty->resolvedFields();
    }

    private function saveCustomFieldValues(Request $request, Patient $patient, Collection $fields): void
    {
        $customFields = $fields->where('is_core', false)->where('is_active', true)->filter(fn($f) => $f->id);
        if ($customFields->isEmpty()) {
            return;
        }

        $textInput       = $request->input('custom', []);
        $removeFiles     = $request->input('remove_custom_file', []);

        foreach ($customFields as $field) {
            $sfId = $field->id;

            if ($field->type === 'file') {
                $fileKey = "custom_file_{$sfId}";

                // Handle remove checkbox
                if (!empty($removeFiles[$sfId])) {
                    $existing = PatientFieldValue::where(['patient_id' => $patient->id, 'specialty_field_id' => $sfId])->first();
                    PatientFiles::delete($existing?->value);
                    PatientFieldValue::updateOrCreate(
                        ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                        ['value' => null]
                    );
                    continue;
                }

                if ($request->hasFile($fileKey)) {
                    // Delete old file if replacing
                    $existing = PatientFieldValue::where(['patient_id' => $patient->id, 'specialty_field_id' => $sfId])->first();
                    PatientFiles::delete($existing?->value);

                    $path = PatientFiles::store($request->file($fileKey), PatientFiles::CUSTOM);
                    PatientFieldValue::updateOrCreate(
                        ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                        ['value' => $path]
                    );
                }
            } else {
                $value = $textInput[$sfId] ?? null;
                $value = is_scalar($value) ? mb_substr((string) $value, 0, 2000) : null;
                PatientFieldValue::updateOrCreate(
                    ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                    ['value' => ($value !== '' && $value !== null) ? $value : null]
                );
            }
        }
    }

    private function authorizePatient(Patient $patient): void
    {
        if ($patient->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }
    }
}
