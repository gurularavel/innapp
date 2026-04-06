<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\SubscriptionPayment;
use App\Services\KapitalBankService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function index()
    {
        $doctor   = Auth::user();
        $current  = $doctor->activeSubscription()->with('package')->first();
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

    /**
     * Initiate payment: create Kapital Bank order and redirect to HPP.
     */
    public function pay(Request $request, Package $package, KapitalBankService $kapital)
    {
        abort_if(!$package->is_active, 404);

        $request->validate(['period' => 'required|in:monthly,annual']);

        $doctor   = Auth::user();
        $isAnnual = $request->period === 'annual';
        $days     = $isAnnual ? $package->duration_days * 12 : $package->duration_days;
        $price    = $isAnnual
            ? round($package->price * 12 * 0.85, 2)
            : (float) $package->price;

        $description = $package->name . ($isAnnual ? ' (İllik)' : ' (Aylıq)');
        $callbackUrl = route('panel.subscription.callback');

        try {
            $order = $kapital->createOrder($price, $description, $callbackUrl);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // Save pending payment record
        SubscriptionPayment::create([
            'doctor_id'                  => $doctor->id,
            'package_id'                 => $package->id,
            'period'                     => $request->period,
            'amount'                     => $price,
            'kapitalbank_order_id'       => $order['id'],
            'kapitalbank_order_password' => $order['password'],
            'status'                     => 'pending',
        ]);

        $hppUrl = $kapital->buildHppUrl($order['hppUrl'], $order['id'], $order['password']);

        return redirect()->away($hppUrl);
    }

    /**
     * Callback after Kapital Bank HPP payment.
     * URL: /panel/subscription/callback?ID={orderId}&STATUS={status}
     */
    public function callback(Request $request, KapitalBankService $kapital)
    {
        $orderId = $request->query('ID');

        if (!$orderId) {
            return redirect()->route('panel.subscription.index')
                ->with('error', 'Ödəniş məlumatı alınmadı.');
        }

        $payment = SubscriptionPayment::where('kapitalbank_order_id', $orderId)
            ->where('doctor_id', Auth::id())
            ->where('status', 'pending')
            ->with('package')
            ->first();

        if (!$payment) {
            return redirect()->route('panel.subscription.index')
                ->with('error', 'Ödəniş tapılmadı.');
        }

        // Verify status via API (don't trust callback STATUS param directly)
        try {
            $orderDetails = $kapital->getOrderDetails((int) $orderId);
        } catch (\Exception $e) {
            Log::error('KapitalBank callback verify failed', ['orderId' => $orderId, 'error' => $e->getMessage()]);
            return redirect()->route('panel.subscription.index')
                ->with('error', 'Ödəniş statusu yoxlanılmadı. Dəstək ilə əlaqə saxlayın.');
        }

        $status = $orderDetails['status'] ?? 'Unknown';

        if ($status !== 'FullyPaid') {
            $payment->update(['status' => 'failed']);
            return redirect()->route('panel.subscription.index')
                ->with('error', 'Ödəniş uğursuz oldu (status: ' . $status . '). Yenidən cəhd edin.');
        }

        $payment->update(['status' => 'paid']);

        // Activate subscription
        [$startsAt, $expiresAt] = $this->activateSubscription($payment);

        return redirect()->route('panel.subscription.success')
            ->with('sub_package', $payment->package->name)
            ->with('sub_period',  $payment->period === 'annual' ? 'İllik' : 'Aylıq')
            ->with('sub_price',   $payment->amount)
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

    /**
     * Create or extend the DoctorSubscription after confirmed payment.
     */
    private function activateSubscription(SubscriptionPayment $payment): array
    {
        $doctor   = $payment->doctor;
        $package  = $payment->package;
        $isAnnual = $payment->period === 'annual';
        $days     = $isAnnual ? $package->duration_days * 12 : $package->duration_days;

        $current = $doctor->subscriptions()
            ->where('is_active', true)
            ->orderByDesc('expires_at')
            ->first();

        if ($current && $current->expires_at->isFuture()) {
            if ($current->package_id === $package->id) {
                // Same package → extend
                $current->expires_at = $current->expires_at->addDays($days);
                $current->save();
                return [$current->starts_at, $current->expires_at];
            } else {
                // Different package → deactivate current, new starts after
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
                return [$startsAt, $expiresAt];
            }
        }

        // No active / expired → fresh from today
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
        return [$startsAt, $expiresAt];
    }
}
