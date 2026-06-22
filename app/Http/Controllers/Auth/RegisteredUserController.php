<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // Linkdən gələn promo kodu (?promo=KOD) formada öncədən doldurmaq üçün
        $promoCode = $request->query('promo');

        return view('auth.register', compact('promoCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ]);

        // Promo kodu yoxla — yalnız istifadəyə yararlıdırsa müştəriyə bağla
        $promoCodeId = null;
        if ($request->filled('promo_code')) {
            $promo = PromoCode::where('code', $request->promo_code)->first();
            if ($promo && $promo->isUsable()) {
                $promoCodeId = $promo->id;
            } else {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['promo_code' => 'Promo kod etibarsız və ya müddəti bitib.']);
            }
        }

        $user = User::create([
            'name'                 => $request->name,
            'surname'              => $request->surname,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'signup_promo_code_id' => $promoCodeId,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('panel.dashboard');
    }
}
