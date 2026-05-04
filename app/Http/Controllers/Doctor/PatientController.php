<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientFieldValue;
use App\Models\SpecialtyField;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
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
                $rules["custom_file_{$cf->id}"] = 'nullable|file|max:10240';
            }
        }

        $validated = $request->validate($rules);
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
            $validated['photo'] = $request->file('photo')->store('patients/photos', 'public');
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
        $patient->load(['appointments.treatmentType', 'visits.files']);
        $customValues = $patient->fieldValues()->with('specialtyField')->get()->keyBy('specialty_field_id');
        $fields = $this->getSpecialtyFields($patient->doctor);
        return view('doctor.patients.show', compact('patient', 'fields', 'customValues'));
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
                $rules["custom_file_{$cf->id}"] = 'nullable|file|max:10240';
            }
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('photo')) {
            if ($patient->photo) {
                Storage::disk('public')->delete($patient->photo);
            }
            $validated['photo'] = $request->file('photo')->store('patients/photos', 'public');
        } elseif ($request->boolean('remove_photo') && $patient->photo) {
            Storage::disk('public')->delete($patient->photo);
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
        $patient->delete();

        return redirect()->route('panel.patients.index')
            ->with('success', 'Müştəri silindi.');
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
                    if ($existing?->value) {
                        Storage::disk('public')->delete($existing->value);
                    }
                    PatientFieldValue::updateOrCreate(
                        ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                        ['value' => null]
                    );
                    continue;
                }

                if ($request->hasFile($fileKey)) {
                    // Delete old file if replacing
                    $existing = PatientFieldValue::where(['patient_id' => $patient->id, 'specialty_field_id' => $sfId])->first();
                    if ($existing?->value) {
                        Storage::disk('public')->delete($existing->value);
                    }

                    $path = $request->file($fileKey)->store('patients/custom_files', 'public');
                    PatientFieldValue::updateOrCreate(
                        ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                        ['value' => $path]
                    );
                }
            } else {
                $value = $textInput[$sfId] ?? null;
                PatientFieldValue::updateOrCreate(
                    ['patient_id' => $patient->id, 'specialty_field_id' => $sfId],
                    ['value' => ($value !== '' && $value !== null) ? $value : null]
                );
            }
        }
    }

    private function authorizePatient(Patient $patient): void
    {
        if ($patient->doctor_id !== Auth::id()) {
            abort(403);
        }
    }
}
