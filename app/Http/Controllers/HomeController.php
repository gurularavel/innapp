<?php

namespace App\Http\Controllers;

use App\Models\Package;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('panel.dashboard');
        }

        $packages = Package::where('is_active', true)->orderBy('price')->get();

        $promoDiscount   = (float) \App\Models\Setting::get('promo_default_discount_percent', 20);
        $promoCommission = (float) \App\Models\Setting::get('promo_default_commission_percent', 5);

        return view('home', compact('packages', 'promoDiscount', 'promoCommission'));
    }
}
