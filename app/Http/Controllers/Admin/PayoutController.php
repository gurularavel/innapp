<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoterPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = PromoterPayout::with('promoter')
            ->latest('requested_at')
            ->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function markPaid(Request $request, PromoterPayout $payout)
    {
        if ($payout->status !== 'requested') {
            return back()->with('error', 'Yalnız gözləyən çıxarış tələbi ödənilə bilər.');
        }

        DB::transaction(function () use ($payout, $request) {
            // Bu çıxarışa bağlı komissiyaları "paid" et
            $payout->redemptions()
                ->where('status', 'available')
                ->update(['status' => 'paid']);

            $payout->update([
                'status'  => 'paid',
                'paid_at' => now(),
                'note'    => $request->input('note', $payout->note),
            ]);
        });

        return back()->with('success', 'Çıxarış ödənildi olaraq işarələndi.');
    }

    public function reject(Request $request, PromoterPayout $payout)
    {
        if ($payout->status !== 'requested') {
            return back()->with('error', 'Yalnız gözləyən çıxarış tələbi rədd edilə bilər.');
        }

        DB::transaction(function () use ($payout, $request) {
            // Komissiyaları yenidən "available" hovuzuna qaytar
            $payout->redemptions()->update(['payout_id' => null]);

            $payout->update([
                'status' => 'rejected',
                'note'   => $request->input('note', $payout->note),
            ]);
        });

        return back()->with('success', 'Çıxarış tələbi rədd edildi, komissiyalar balansa qaytarıldı.');
    }
}
