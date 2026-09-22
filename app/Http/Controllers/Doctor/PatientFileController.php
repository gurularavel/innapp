<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientFieldValue;
use App\Models\PatientVisitFile;
use App\Support\PatientFiles;
use Illuminate\Support\Facades\Auth;

/**
 * Streams patient uploads to members of the clinic that owns the patient.
 *
 * Every file is looked up through its database record, so a URL can only ever
 * reach a file the record points to, and the record carries the clinic check.
 */
class PatientFileController extends Controller
{
    public function photo(Patient $patient)
    {
        $this->authorizePatient($patient);

        abort_unless($patient->photo, 404);

        return PatientFiles::response($patient->photo);
    }

    public function visitFile(PatientVisitFile $file)
    {
        $visit = $file->visit;

        abort_unless($visit && $visit->clinic_id === Auth::user()->clinic_id, 404);

        return PatientFiles::response($file->file_path, $file->original_name);
    }

    public function customFile(Patient $patient, int $field)
    {
        $this->authorizePatient($patient);

        $value = PatientFieldValue::where('patient_id', $patient->id)
            ->where('specialty_field_id', $field)
            ->first();

        abort_unless($value?->value, 404);

        return PatientFiles::response($value->value);
    }

    private function authorizePatient(Patient $patient): void
    {
        abort_unless($patient->clinic_id === Auth::user()->clinic_id, 404);
    }
}
