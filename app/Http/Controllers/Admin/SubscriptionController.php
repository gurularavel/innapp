<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = DoctorSubscription::with('doctor', 'package')
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $doctors = User::where('role', 'doctor')->where('is_active', true)->get();
        $packages = Package::where('is_active', true)->get();
        return view('admin.subscriptions.create', compact('doctors', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'starts_at' => 'required|date',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);

        // Deactivate existing active subscriptions
        DoctorSubscription::where('doctor_id', $validated['doctor_id'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        DoctorSubscription::create([
            'doctor_id' => $validated['doctor_id'],
            'package_id' => $validated['package_id'],
            'starts_at' => $startsAt->toDateString(),
            'expires_at' => $startsAt->addDays($package->duration_days)->toDateString(),
            'patients_used' => 0,
            'sms_used' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abunəlik uğurla yaradıldı.');
    }

    public function destroy(DoctorSubscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abunəlik silindi.');
    }
}
