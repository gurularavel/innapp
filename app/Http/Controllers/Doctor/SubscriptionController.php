<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\PromoRedemption;
use App\Models\SubscriptionPayment;
use App\Services\KapitalBankService;
use App\Services\SmsService;
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
        $doctor  = Auth::user();
        $current = $doctor->activeSubscription()->with('package')->first();

        // İlk ödəniş promosu (varsa) — qiymət xülasəsində endirimi göstərmək üçün
        $promo   = $this->resolveFirstPaymentPromo($doctor);
        $monthly = $this->computePrice($package, false, $promo);
        $annual  = $this->computePrice($package, true, $promo);

        return view('doctor.subscription.checkout', compact('package', 'current', 'promo', 'monthly', 'annual'));
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

        // Promo endirimi — yalnız ilk ödənişdə və kod hələ də yararlıdırsa
        $promo    = $this->resolveFirstPaymentPromo($doctor);
        $pricing  = $this->computePrice($package, $isAnnual, $promo);
        $price    = $pricing['final'];
        $discount = $pricing['discount'];

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
            'promo_code_id'              => $promo?->id,
            'period'                     => $request->period,
            'amount'                     => $price,
            'discount_amount'            => $discount,
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

        // Promotor komissiyasını yaz (yalnız ilk uğurlu ödənişdə)
        $this->recordPromoCommission($payment);

        // Notify admin
        try {
            $doctor  = $payment->doctor;
            $message = "{$doctor->name} {$doctor->surname} - {$payment->package->name} paketi aldı. Məbləğ: {$payment->amount} AZN. Ödəniş qəbul olundu.";
            app(SmsService::class)->send('+994557038008', $message);
        } catch (\Exception $e) {
            Log::warning('Admin SMS notification failed: ' . $e->getMessage());
        }

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
     * Paket + dövr + promo üçün qiymət bölgüsü.
     * Qayda: illik 15% və promo endirimindən böyük olanı tətbiq olunur (üst-üstə gəlmir).
     */
    private function computePrice(Package $package, bool $isAnnual, ?\App\Models\PromoCode $promo): array
    {
        $listPrice      = $isAnnual ? (float) $package->price * 12 : (float) $package->price;
        $annualDiscount = $isAnnual ? round($listPrice * 0.15, 2) : 0.0;
        $promoDiscount  = $promo ? $promo->discountFor($listPrice) : 0.0;
        $discount       = round(max($annualDiscount, $promoDiscount), 2);

        return [
            'list'            => round($listPrice, 2),
            'annual_discount' => $annualDiscount,
            'promo_discount'  => round($promoDiscount, 2),
            'discount'        => $discount,
            'final'           => round($listPrice - $discount, 2),
            // Promo bu dövr üçün qalib gəldimi (yəni illik 15%-dən böyük və ya bərabər)?
            'promo_wins'      => $promo && $promoDiscount > 0 && $promoDiscount >= $annualDiscount,
        ];
    }

    /**
     * Müştərinin qeydiyyatda bağladığı promo kodu — yalnız bu onun ilk ödənişidirsə
     * və kod hələ də yararlıdırsa qaytarır. Əks halda null.
     */
    private function resolveFirstPaymentPromo($doctor): ?\App\Models\PromoCode
    {
        $promo = $doctor->signupPromoCode;

        if (!$promo || !$promo->isUsable()) {
            return null;
        }

        // İlk ödəniş? Əvvəl uğurlu ödənişi olubsa — promo tətbiq olunmur
        $hasPaid = SubscriptionPayment::where('doctor_id', $doctor->id)
            ->where('status', 'paid')
            ->exists();

        if ($hasPaid) {
            return null;
        }

        return $promo;
    }

    /**
     * Təsdiqlənmiş ilk ödənişdən sonra promotor komissiyasını yazır.
     * Komissiya 14 gün "pending" qalır (refund riski), sonra "available" olur.
     */
    private function recordPromoCommission(SubscriptionPayment $payment): void
    {
        if (!$payment->promo_code_id) {
            return;
        }

        // Hər müştəri üçün yalnız bir dəfə komissiya (təkrar callback-a qarşı qoruyucu)
        $exists = PromoRedemption::where('customer_id', $payment->doctor_id)->exists();
        if ($exists) {
            return;
        }

        $promo = $payment->promoCode;
        if (!$promo) {
            return;
        }

        $commission = $promo->commissionFor((float) $payment->amount);

        PromoRedemption::create([
            'promo_code_id'           => $promo->id,
            'promoter_id'             => $promo->promoter_id,
            'customer_id'             => $payment->doctor_id,
            'subscription_payment_id' => $payment->id,
            'discount_applied'        => (float) $payment->discount_amount,
            'commission_amount'       => $commission,
            'status'                  => 'pending',
            'available_at'            => now()->addDays(14),
        ]);

        $promo->increment('used_count');
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
