<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorSubscription;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $doctor  = Auth::user();
        $current = $doctor->activeSubscription()->with('package')->first();
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $history  = $doctor->subscriptions()->with('package')
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get();

        return view('doctor.subscription.index', compact('current', 'packages', 'history'));
    }

    public function checkout(Package $package)
    {
        abort_if(!$package->is_active, 404);
        $current = Auth::user()->activeSubscription()->with('package')->first();

        return view('doctor.subscription.checkout', compact('package', 'current'));
    }

    public function pay(Request $r, Package $package)
    {
        abort_if(!$package->is_active, 404);

        $r->validate(['period' => 'required|in:monthly,annual']);

        $doctor   = Auth::user();
        $isAnnual = $r->period === 'annual';
        $days     = $isAnnual ? $package->duration_days * 12 : $package->duration_days;
        $price    = $isAnnual ? round($package->price * 12 * 0.85, 2) : $package->price;

        $current = $doctor->subscriptions()
            ->where('is_active', true)
            ->orderByDesc('expires_at')
            ->first();

        if ($current && $current->expires_at->isFuture()) {
            if ($current->package_id === $package->id) {
                // Same package → extend expires_at in place
                $current->expires_at = $current->expires_at->addDays($days);
                $current->save();
                $startsAt  = $current->starts_at;
                $expiresAt = $current->expires_at;
            } else {
                // Different package → deactivate current, new starts after it
                $startsAt  = (clone $current->expires_at)->addDay();
                $expiresAt = (clone $startsAt)->addDays($days);
                $doctor->subscriptions()->where('is_active', true)->update(['is_active' => false]);
                DoctorSubscription::create([
                    'doctor_id'     => $doctor->id,
                    'package_id'    => $package->id,
                    'starts_at'     => $startsAt->toDateString(),
                    'expires_at'    => $expiresAt->toDateString(),
                    'patients_used' => 0,
                    'sms_used'      => 0,
                    'is_active'     => true,
                ]);
            }
        } else {
            // No active / expired → create fresh from today
            $startsAt  = now();
            $expiresAt = now()->addDays($days);
            $doctor->subscriptions()->where('is_active', true)->update(['is_active' => false]);
            DoctorSubscription::create([
                'doctor_id'     => $doctor->id,
                'package_id'    => $package->id,
                'starts_at'     => $startsAt->toDateString(),
                'expires_at'    => $expiresAt->toDateString(),
                'patients_used' => 0,
                'sms_used'      => 0,
                'is_active'     => true,
            ]);
        }

        return redirect()->route('panel.subscription.success')
            ->with('sub_package', $package->name)
            ->with('sub_period',  $isAnnual ? 'İllik' : 'Aylıq')
            ->with('sub_price',   $price)
            ->with('sub_starts',  Carbon::parse($startsAt)->format('d.m.Y'))
            ->with('sub_expires', Carbon::parse($expiresAt)->format('d.m.Y'));
    }

    public function success()
    {
        if (!session()->has('sub_package')) {
            return redirect()->route('panel.subscription.index');
        }

        return view('doctor.subscription.success');
    }
}
