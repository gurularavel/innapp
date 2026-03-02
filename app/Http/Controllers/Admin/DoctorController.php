<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = User::where('role', 'doctor')
            ->with('specialty', 'activeSubscription.package')
            ->latest()
            ->paginate(15);

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $specialties = Specialty::where('is_active', true)->get();
        return view('admin.doctors.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'specialty_id' => 'nullable|exists:specialties,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'doctor';
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Həkim uğurla yaradıldı.');
    }

    public function show(User $doctor)
    {
        $doctor->load('specialty', 'subscriptions.package', 'patients', 'appointments');
        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(User $doctor)
    {
        $specialties = Specialty::where('is_active', true)->get();
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    public function update(Request $request, User $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'specialty_id' => 'nullable|exists:specialties,id',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', false);

        $doctor->update($validated);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Həkim məlumatları yeniləndi.');
    }

    public function destroy(User $doctor)
    {
        $doctor->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', 'Həkim silindi.');
    }

    public function toggleStatus(User $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        $status = $doctor->is_active ? 'aktiv edildi' : 'deaktiv edildi';
        return back()->with('success', "Həkim {$status}.");
    }
}
