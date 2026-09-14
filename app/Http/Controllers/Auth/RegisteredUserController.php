<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\VerifiesCaptcha;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\Specialty;
use App\Models\User;
use App\Rules\AzMobilePhone;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use VerifiesCaptcha;

    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // Linkdən gələn promo kodu (?promo=KOD) formada öncədən doldurmaq üçün
        $promoCode = $request->query('promo');

        // Admindən tənzimlənən istifadə qaydaları (modal-da göstərilir)
        $termsTitle   = Setting::get('terms_title', 'İstifadə Qaydaları');
        $termsContent = Setting::get('terms_content', '');

        // Vəzifə / ixtisas seçimi üçün aktiv ixtisaslar
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('auth.register', compact('promoCode', 'termsTitle', 'termsContent', 'specialties'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            ...$this->captchaRules($request, 'register'),
            'name'    => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'   => ['required', 'string', 'max:20', new AzMobilePhone],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'specialty_id' => ['nullable', 'exists:specialties,id'],
            'account_type' => ['nullable', 'in:solo,clinic'],
            'clinic_name' => ['nullable', 'string', 'max:100'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'terms' => ['accepted'],
        ], [
            ...$this->captchaMessages(),
            'phone.required' => 'Mobil nömrə vacibdir.',
            'terms.accepted' => 'Davam etmək üçün istifadə qaydalarını qəbul etməlisiniz.',
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

        // Every account lives inside a clinic. A solo specialist simply gets a
        // one-person clinic named after them, so both flows share one structure.
        $clinicName = $request->account_type === 'clinic' && $request->filled('clinic_name')
            ? $request->clinic_name
            : trim($request->name . ' ' . $request->surname);

        $clinic = Clinic::create([
            'name'      => $clinicName,
            'is_active' => true,
        ]);

        $user = User::create([
            'clinic_id'            => $clinic->id,
            'name'                 => $request->name,
            'surname'              => $request->surname,
            'email'                => $request->email,
            'phone'                => AzMobilePhone::format($request->phone),
            'password'             => Hash::make($request->password),
            'role'                 => 'owner',
            'specialty_id'         => $request->specialty_id,
            'takes_appointments'   => true,
            'is_active'            => true,
            'signup_promo_code_id' => $promoCodeId,
        ]);

        $clinic->update(['owner_id' => $user->id]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('panel.dashboard');
    }
}
