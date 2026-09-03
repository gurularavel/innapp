<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    /**
     * Every registered clinic account, paying or not.
     *
     * Registration creates `owner`s, so filtering on `doctor` alone hid the
     * whole customer base. Billing is per clinic, so the subscription filter
     * asks the clinic, not the individual account.
     */
    public function index(Request $request)
    {
        $query = User::staff()->with('clinic.activeSubscription.package', 'specialty');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('clinic', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if (in_array($request->input('role'), User::CLINIC_ROLES, true)) {
            $query->where('role', $request->input('role'));
        }

        if ($request->input('subscription') === 'active') {
            $query->whereHas('clinic.activeSubscription');
        } elseif ($request->input('subscription') === 'none') {
            $query->whereDoesntHave('clinic.activeSubscription');
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->input('demo') === 'hide') {
            $query->where('is_demo', false);
        } elseif ($request->input('demo') === 'only') {
            $query->where('is_demo', true);
        }

        $doctors = $query->latest()->paginate(20)->withQueryString();

        $base  = User::staff();
        $stats = [
            'total'      => (clone $base)->count(),
            'subscribed' => (clone $base)->whereHas('clinic.activeSubscription')->count(),
            'unpaid'     => (clone $base)->whereDoesntHave('clinic.activeSubscription')->count(),
            'demo'       => (clone $base)->where('is_demo', true)->count(),
        ];

        return view('admin.users.index', compact('doctors', 'stats'));
    }

    public function create()
    {
        $specialties = Specialty::where('is_active', true)->get();
        return view('admin.users.create', compact('specialties'));
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

        return redirect()->route('admin.users.index')
            ->with('success', 'İstifadəçi uğurla yaradıldı.');
    }

    public function show(User $doctor)
    {
        $doctor->load('specialty', 'clinic.activeSubscription.package', 'subscriptions.package');
        $patientsCount     = $doctor->patients()->count();
        $appointmentsCount = $doctor->appointments()->count();
        return view('admin.users.show', compact('doctor', 'patientsCount', 'appointmentsCount'));
    }

    public function edit(User $doctor)
    {
        $specialties = Specialty::where('is_active', true)->get();
        return view('admin.users.edit', compact('doctor', 'specialties'));
    }

    public function update(Request $request, User $doctor)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'surname'      => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $doctor->id,
            'phone'        => 'nullable|string|max:20',
            'password'     => 'nullable|min:8|confirmed',
            'specialty_id' => 'nullable|exists:specialties,id',
            'is_active'    => 'boolean',
        ]);

        $emailChanged = $doctor->email !== $validated['email'];

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', false);

        // If email changed, invalidate all active sessions and remember token
        if ($emailChanged) {
            $validated['remember_token'] = Str::random(60);
            DB::table('sessions')->where('user_id', $doctor->id)->delete();
        }

        $doctor->update($validated);

        $message = 'İstifadəçi məlumatları yeniləndi.';
        if ($emailChanged) {
            $message .= ' E-poçt dəyişdirildiyindən aktiv sessiyalar sonlandırıldı.';
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function destroy(User $doctor)
    {
        $doctor->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'İstifadəçi silindi.');
    }

    public function toggleStatus(User $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        $status = $doctor->is_active ? 'aktiv edildi' : 'deaktiv edildi';
        return back()->with('success', "İstifadəçi {$status}.");
    }
}
