<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = DoctorSubscription::with('doctor', 'clinic', 'package')
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $doctors = User::staff()
            ->where('is_active', true)
            ->with('clinic')
            ->orderBy('name')
            ->get();

        $packages = Package::where('is_active', true)->get();

        return view('admin.subscriptions.create', compact('doctors', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id'  => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'starts_at'  => 'required|date',
            'seats'      => 'nullable|integer|min:1|max:500',
            'months'     => 'nullable|integer|min:1|max:36',
        ]);

        $doctor = User::findOrFail($validated['doctor_id']);

        if (! $doctor->isClinicMember() || ! $doctor->clinic_id) {
            return back()->withInput()
                ->with('error', 'Seçilmiş istifadəçi heç bir müəssisəyə bağlı deyil, ona abunəlik verilə bilməz.');
        }

        $package  = Package::findOrFail($validated['package_id']);
        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
        $months   = max(1, (int) ($validated['months'] ?? 1));

        // Seats must at least cover the accounts the clinic already has,
        // otherwise CheckSubscription would block the panel straight away.
        $usedSeats = $doctor->clinic?->usedSeats() ?? 1;
        $seats     = max((int) ($validated['seats'] ?? 0), $usedSeats, $package->min_seats, 1);

        if ($package->max_seats !== null) {
            $seats = min($seats, $package->max_seats);
        }

        // Billing is per clinic — retire whatever the clinic had before.
        DoctorSubscription::where('clinic_id', $doctor->clinic_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        DoctorSubscription::create([
            'clinic_id'      => $doctor->clinic_id,
            'doctor_id'      => $doctor->id,
            'package_id'     => $package->id,
            'seats'          => $seats,
            'price_per_seat' => $package->price_per_seat,
            'starts_at'      => $startsAt->toDateString(),
            'expires_at'     => (clone $startsAt)->addDays($package->duration_days * $months)->toDateString(),
            'patients_used'  => 0,
            'is_active'      => true,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abunəlik uğurla yaradıldı.');
    }

    public function edit(DoctorSubscription $subscription)
    {
        $subscription->load('doctor', 'clinic', 'package');

        $packages = Package::where('is_active', true)
            ->orWhere('id', $subscription->package_id)
            ->get();

        return view('admin.subscriptions.edit', compact('subscription', 'packages'));
    }

    public function update(Request $request, DoctorSubscription $subscription)
    {
        $validated = $request->validate([
            'package_id'    => 'required|exists:packages,id',
            'starts_at'     => 'required|date',
            'expires_at'    => 'required|date|after_or_equal:starts_at',
            'seats'         => 'required|integer|min:1|max:500',
            'patients_used' => 'required|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);

        $package  = Package::findOrFail($validated['package_id']);
        $seats    = (int) $validated['seats'];
        $isActive = $request->boolean('is_active');

        // Seats must still cover the accounts the clinic has, otherwise
        // CheckSubscription would block the panel right after this save.
        $usedSeats = $subscription->clinic?->usedSeats() ?? 0;
        if ($seats < $usedSeats) {
            return back()->withInput()
                ->with('error', "Yer sayı müəssisədəki aktiv hesab sayından ({$usedSeats}) az ola bilməz.");
        }

        if (! $package->allowsSeats($seats)) {
            return back()->withInput()
                ->with('error', 'Seçilmiş paket bu yer sayına icazə vermir.');
        }

        // Only one subscription may be active per clinic.
        if ($isActive && $subscription->clinic_id) {
            DoctorSubscription::where('clinic_id', $subscription->clinic_id)
                ->where('id', '!=', $subscription->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $subscription->update([
            'package_id'     => $package->id,
            'seats'          => $seats,
            'price_per_seat' => $package->price_per_seat,
            'starts_at'      => \Carbon\Carbon::parse($validated['starts_at'])->toDateString(),
            'expires_at'     => \Carbon\Carbon::parse($validated['expires_at'])->toDateString(),
            'patients_used'  => (int) $validated['patients_used'],
            'is_active'      => $isActive,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abunəlik yeniləndi.');
    }

    /** Quick "+N gün/ay/il" from the list, without opening the form. */
    public function extend(Request $request, DoctorSubscription $subscription)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $days = (int) $validated['days'];

        // An expired subscription restarts from today, a live one keeps its tail.
        $base = $subscription->expires_at->isPast() ? now()->startOfDay() : $subscription->expires_at;

        $subscription->update([
            'expires_at' => $base->copy()->addDays($days)->toDateString(),
            'is_active'  => true,
        ]);

        if ($subscription->clinic_id) {
            DoctorSubscription::where('clinic_id', $subscription->clinic_id)
                ->where('id', '!=', $subscription->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return back()->with('success', "Abunəlik {$days} gün uzadıldı — yeni bitmə tarixi: "
            . $subscription->expires_at->format('d.m.Y') . '.');
    }

    public function destroy(DoctorSubscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abunəlik silindi.');
    }

    public function payments(Request $request)
    {
        $query = SubscriptionPayment::with('doctor', 'package')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20)->withQueryString();

        $stats = [
            'total_paid'   => SubscriptionPayment::where('status', 'paid')->sum('amount'),
            'count_paid'   => SubscriptionPayment::where('status', 'paid')->count(),
            'count_pending' => SubscriptionPayment::where('status', 'pending')->count(),
            'count_failed' => SubscriptionPayment::where('status', 'failed')->count(),
        ];

        $doctors = User::staff()->orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'stats', 'doctors'));
    }
}
