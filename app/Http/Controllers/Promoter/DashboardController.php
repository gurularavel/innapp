<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $promoter = Auth::user();
        $balances = $promoter->commissionBalances();

        $codes = $promoter->promoCodes()
            ->withCount('redemptions')
            ->latest()
            ->get();

        $totalCustomers = $promoter->redemptions()->count();

        $recent = $promoter->redemptions()
            ->with(['customer', 'promoCode', 'payment.package'])
            ->latest()
            ->limit(10)
            ->get();

        return view('promoter.dashboard', compact('promoter', 'balances', 'codes', 'totalCustomers', 'recent'));
    }

    public function codes()
    {
        $promoter = Auth::user();
        $codes = $promoter->promoCodes()
            ->withCount('redemptions')
            ->latest()
            ->paginate(20);

        return view('promoter.codes', compact('codes'));
    }

    public function redemptions()
    {
        $promoter = Auth::user();
        $redemptions = $promoter->redemptions()
            ->with(['customer', 'promoCode', 'payment.package'])
            ->latest()
            ->paginate(30);

        return view('promoter.redemptions', compact('redemptions'));
    }
}
