<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Staff management for a clinic owner.
 *
 * Every account is a paid seat, so adding a member is blocked once the clinic
 * has used up the seats it subscribed for.
 */
class StaffController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                abort_unless(
                    Auth::user()?->canManageClinic(),
                    403,
                    'Yalnız klinika sahibi əməkdaşları idarə edə bilər.'
                );

                return $next($request);
            },
        ];
    }

    public function index()
    {
        $clinic = Auth::user()->clinic;

        $staff = $clinic->members()
            ->with('specialty')
            ->orderByRaw("CASE role WHEN 'owner' THEN 1 WHEN 'doctor' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();

        $subscription = $clinic->activeSubscription()->with('package')->first();

        return view('doctor.staff.index', compact('clinic', 'staff', 'subscription'));
    }

    public function create()
    {
        $clinic = Auth::user()->clinic;

        if (! $clinic->canAddMember()) {
            return redirect()->route('panel.staff.index')
                ->with('error', 'Bütün yerlər doludur. Yeni əməkdaş üçün abunənizdəki yer sayını artırın.');
        }

        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('doctor.staff.create', compact('clinic', 'specialties'));
    }

    public function store(Request $request)
    {
        $clinic = Auth::user()->clinic;

        if (! $clinic->canAddMember()) {
            return redirect()->route('panel.staff.index')
                ->with('error', 'Bütün yerlər doludur. Yeni əməkdaş üçün abunənizdəki yer sayını artırın.');
        }

        $validated = $request->validate($this->rules());

        User::create([
            'clinic_id'          => $clinic->id,
            'name'               => $validated['name'],
            'surname'            => $validated['surname'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'password'           => Hash::make($validated['password']),
            'role'               => $validated['role'],
            'specialty_id'       => $validated['specialty_id'] ?? null,
            'job_title'          => $validated['job_title'] ?? null,
            'takes_appointments' => $validated['role'] === 'receptionist'
                ? false
                : $request->boolean('takes_appointments', true),
            'is_active'          => true,
        ]);

        return redirect()->route('panel.staff.index')
            ->with('success', 'Əməkdaş əlavə edildi.');
    }

    public function edit(User $staff)
    {
        $this->authorizeMember($staff);

        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $clinic      = Auth::user()->clinic;

        return view('doctor.staff.edit', compact('staff', 'specialties', 'clinic'));
    }

    public function update(Request $request, User $staff)
    {
        $this->authorizeMember($staff);

        $validated = $request->validate($this->rules($staff));

        // The owner must stay the owner — otherwise a clinic could end up unmanaged.
        $role = $staff->id === $staff->clinic->owner_id ? 'owner' : $validated['role'];

        $data = [
            'name'               => $validated['name'],
            'surname'            => $validated['surname'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'role'               => $role,
            'specialty_id'       => $validated['specialty_id'] ?? null,
            'job_title'          => $validated['job_title'] ?? null,
            'takes_appointments' => $role === 'receptionist'
                ? false
                : $request->boolean('takes_appointments', true),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        return redirect()->route('panel.staff.index')
            ->with('success', 'Əməkdaş məlumatları yeniləndi.');
    }

    /** Deactivating frees the seat without destroying the member's history. */
    public function toggleStatus(User $staff)
    {
        $this->authorizeMember($staff);

        if ($staff->id === $staff->clinic->owner_id) {
            return back()->with('error', 'Klinika sahibinin hesabı deaktiv edilə bilməz.');
        }

        if (! $staff->is_active && ! $staff->clinic->canAddMember()) {
            return back()->with('error', 'Boş yer yoxdur. Əvvəlcə abunənizdəki yer sayını artırın.');
        }

        $staff->update(['is_active' => ! $staff->is_active]);

        return back()->with('success', $staff->is_active ? 'Əməkdaş aktivləşdirildi.' : 'Əməkdaş deaktiv edildi.');
    }

    public function destroy(User $staff)
    {
        $this->authorizeMember($staff);

        if ($staff->id === $staff->clinic->owner_id) {
            return back()->with('error', 'Klinika sahibi silinə bilməz.');
        }

        if ($staff->appointments()->exists()) {
            return back()->with('error', 'Bu əməkdaşın randevuları var. Silmək əvəzinə hesabı deaktiv edin.');
        }

        $staff->delete();

        return redirect()->route('panel.staff.index')
            ->with('success', 'Əməkdaş silindi.');
    }

    // -------------------------------------------------------------------------

    private function rules(?User $staff = null): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'surname'            => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff?->id)],
            'phone'              => ['nullable', 'string', 'max:20'],
            'role'               => ['required', Rule::in(['owner', 'doctor', 'receptionist'])],
            'specialty_id'       => ['nullable', 'exists:specialties,id'],
            'job_title'          => ['nullable', 'string', 'max:100'],
            'takes_appointments' => ['boolean'],
            'password'           => $staff
                ? ['nullable', 'confirmed', Password::min(8)]
                : ['required', 'confirmed', Password::min(8)],
        ];
    }

    /** A clinic owner may only touch members of their own clinic. */
    private function authorizeMember(User $staff): void
    {
        abort_unless($staff->clinic_id === Auth::user()->clinic_id, 403);
    }
}
