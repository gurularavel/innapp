<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PromoterRegistrationController extends Controller
{
    /**
     * Promotor qeydiyyat formu.
     */
    public function create(): View
    {
        $termsTitle   = Setting::get('terms_title', 'İstifadə Qaydaları');
        $termsContent = Setting::get('terms_content', '');

        $discountPercent   = (float) Setting::get('promo_default_discount_percent', 20);
        $commissionPercent = (float) Setting::get('promo_default_commission_percent', 5);

        return view('auth.promoter-register', compact(
            'termsTitle', 'termsContent', 'discountPercent', 'commissionPercent'
        ));
    }

    /**
     * Promotoru qeydiyyatdan keçirir və şəxsi promo kodunu avtomatik yaradır.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms'    => ['accepted'],
        ], [
            'terms.accepted' => 'Davam etmək üçün istifadə qaydalarını qəbul etməlisiniz.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'      => $request->name,
                'surname'   => $request->surname,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'role'      => 'promoter',
                'is_active' => true,
            ]);

            // Defolt faizlər admin ayarlarından götürülür (settings.promo)
            PromoCode::create([
                'code'             => $this->generateUniqueCode($request->name, $request->surname),
                'promoter_id'      => $user->id,
                'discount_type'    => 'percent',
                'discount_value'   => (float) Setting::get('promo_default_discount_percent', 20),
                'commission_type'  => 'percent',
                'commission_value' => (float) Setting::get('promo_default_commission_percent', 5),
                'is_active'        => true,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('promoter.dashboard')
            ->with('success', 'Xoş gəldiniz! Şəxsi promo kodunuz avtomatik yaradıldı — onu "Kodlarım" bölməsində görə bilərsiniz.');
    }

    /**
     * Ad/soyaddan unikal promo kod yaradır (məs. ELCHX7K2).
     */
    private function generateUniqueCode(string $name, string $surname): string
    {
        $base = strtoupper(Str::slug($name . $surname, ''));
        $base = substr($base, 0, 4) ?: 'PROMO';

        do {
            $code = $base . strtoupper(Str::random(4));
        } while (PromoCode::where('code', $code)->exists());

        return $code;
    }
}
