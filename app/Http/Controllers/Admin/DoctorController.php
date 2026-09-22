<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'password' => ['required', 'confirmed', Password::defaults()],
            'specialty_id' => 'nullable|exists:specialties,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Every account lives in a clinic (see registration): an admin-created
        // one becomes the owner of a fresh single-member clinic.
        DB::transaction(function () use ($validated) {
            $clinic = Clinic::create([
                'name'      => trim($validated['name'] . ' ' . $validated['surname']),
                'is_active' => true,
            ]);

            $user = User::create($validated + [
                'clinic_id'          => $clinic->id,
                'role'               => 'owner',
                'takes_appointments' => true,
            ]);

            $clinic->update(['owner_id' => $user->id]);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'İstifadəçi uğurla yaradıldı.');
    }

    public function show(User $doctor)
    {
        $this->onlyClinicAccounts($doctor);

        $doctor->load('specialty', 'clinic.activeSubscription.package', 'subscriptions.package');
        $patientsCount     = $doctor->patients()->count();
        $appointmentsCount = $doctor->appointments()->count();
        return view('admin.users.show', compact('doctor', 'patientsCount', 'appointmentsCount'));
    }

    public function edit(User $doctor)
    {
        $this->onlyClinicAccounts($doctor);

        $specialties = Specialty::where('is_active', true)->get();
        return view('admin.users.edit', compact('doctor', 'specialties'));
    }

    public function update(Request $request, User $doctor)
    {
        $this->onlyClinicAccounts($doctor);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'surname'      => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $doctor->id,
            'phone'        => 'nullable|string|max:20',
            'password'     => ['nullable', 'confirmed', Password::defaults()],
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
        $doctor->update($validated);

        // New e-mail, new password or a deactivation: every existing session ends.
        if (($doctor->wasChanged('password') || $emailChanged || ! $doctor->is_active) && $doctor->id !== Auth::id()) {
            $doctor->revokeSessions();
        }

        $message = 'İstifadəçi məlumatları yeniləndi.';
        if ($emailChanged) {
            $message .= ' E-poçt dəyişdirildiyindən aktiv sessiyalar sonlandırıldı.';
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function destroy(User $doctor)
    {
        $this->onlyClinicAccounts($doctor);

        $doctor->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'İstifadəçi silindi.');
    }

    /**
     * This screen manages clinic accounts. Admins and promoters have their own
     * screens with their own guards (e.g. "the last admin cannot be deleted"),
     * which this resource must not be a way around.
     */
    private function onlyClinicAccounts(User $doctor): void
    {
        abort_unless($doctor->isClinicMember(), 404);
    }

    public function toggleStatus(User $doctor)
    {
        $this->onlyClinicAccounts($doctor);

        $doctor->update(['is_active' => !$doctor->is_active]);

        if (! $doctor->is_active) {
            $doctor->revokeSessions();
        }
        $status = $doctor->is_active ? 'aktiv edildi' : 'deaktiv edildi';
        return back()->with('success', "İstifadəçi {$status}.");
    }
}
