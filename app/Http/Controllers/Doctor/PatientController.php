<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('doctor.patients.create');
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
        $subscription = $doctor->activeSubscription()->with('package')->first();

        if ($subscription && $subscription->patientLimitReached()) {
            return redirect()->route('panel.patients.index')
                ->with('error', 'Müştəri limitinə çatdınız. Paketi yeniləyin.');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'surname'        => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'birth_date'     => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'weight'         => 'nullable|numeric|min:0|max:999',
            'blood_type'     => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'notes'          => 'nullable|string',
        ]);

        $validated['doctor_id'] = $doctor->id;

        Patient::create($validated);

        if ($subscription) {
            $subscription->increment('patients_used');
        }

        return redirect()->route('panel.patients.index')
            ->with('success', 'Müştəri uğurla qeydə alındı.');
    }

    public function show(Patient $patient)
    {
        $this->authorizePatient($patient);
        $patient->load('appointments.treatmentType');
        return view('doctor.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        $this->authorizePatient($patient);
        return view('doctor.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'surname'        => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'birth_date'     => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'weight'         => 'nullable|numeric|min:0|max:999',
            'blood_type'     => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'notes'          => 'nullable|string',
        ]);

        $patient->update($validated);

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

    private function authorizePatient(Patient $patient): void
    {
        if ($patient->doctor_id !== Auth::id()) {
            abort(403);
        }
    }
}
