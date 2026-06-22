<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\PromoterPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $promoter = Auth::user();
        $balances = $promoter->commissionBalances();
        $payouts  = $promoter->payouts()->latest()->paginate(15);

        return view('promoter.payouts', compact('balances', 'payouts'));
    }

    public function store(Request $request)
    {
        $promoter = Auth::user();

        $request->validate([
            'method' => ['required', 'string', 'max:255'],
            'note'   => ['nullable', 'string', 'max:500'],
        ], [], [
            'method' => 'ödəniş rekvizitləri',
        ]);

        // Gözləyən çıxarış varsa yenisini qəbul etmə
        if ($promoter->payouts()->where('status', 'requested')->exists()) {
            return back()->with('error', 'Artıq gözləyən çıxarış tələbiniz var. Təsdiqini gözləyin.');
        }

        if ($promoter->commissionBalances()['available'] <= 0) {
            return back()->with('error', 'Çıxarıla bilən balansınız yoxdur.');
        }

        DB::transaction(function () use ($promoter, $request) {
            // Çıxarıla bilən (available) və hələ heç bir çıxarışa bağlanmamış komissiyalar
            $available = $promoter->redemptions()
                ->where('status', 'available')
                ->whereNull('payout_id')
                ->lockForUpdate()
                ->get();

            $amount = round((float) $available->sum('commission_amount'), 2);

            if ($amount <= 0) {
                return;
            }

            $payout = PromoterPayout::create([
                'promoter_id'  => $promoter->id,
                'amount'       => $amount,
                'status'       => 'requested',
                'method'       => $request->method,
                'note'         => $request->note,
                'requested_at' => now(),
            ]);

            // Komissiyaları bu çıxarışa bağla (ödənişə qədər kilidlənir)
            $promoter->redemptions()
                ->whereIn('id', $available->pluck('id'))
                ->update(['payout_id' => $payout->id]);
        });

        return back()->with('success', 'Çıxarış tələbiniz qeydə alındı. Təsdiq gözlənilir.');
    }
}
