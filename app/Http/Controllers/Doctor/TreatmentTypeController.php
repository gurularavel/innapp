<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\TreatmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentTypeController extends Controller
{
    public function index()
    {
        $treatmentTypes = Auth::user()->treatmentTypes()->latest()->paginate(15);
        return view('doctor.treatment-types.index', compact('treatmentTypes'));
    }

    public function create()
    {
        return view('doctor.treatment-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['doctor_id'] = Auth::id();

        TreatmentType::create($validated);

        return redirect()->route('panel.treatment-types.index')
            ->with('success', 'Müalicə növü yaradıldı.');
    }

    public function edit(TreatmentType $treatmentType)
    {
        $this->authorize($treatmentType);
        return view('doctor.treatment-types.edit', compact('treatmentType'));
    }

    public function update(Request $request, TreatmentType $treatmentType)
    {
        $this->authorize($treatmentType);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $treatmentType->update($validated);

        return redirect()->route('panel.treatment-types.index')
            ->with('success', 'Müalicə növü yeniləndi.');
    }

    public function destroy(TreatmentType $treatmentType)
    {
        $this->authorize($treatmentType);
        $treatmentType->delete();

        return redirect()->route('panel.treatment-types.index')
            ->with('success', 'Müalicə növü silindi.');
    }

    private function authorize(TreatmentType $treatmentType): void
    {
        if ($treatmentType->doctor_id !== Auth::id()) {
            abort(403);
        }
    }
}
