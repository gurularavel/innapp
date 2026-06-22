<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::withCount('redemptions')
            ->with('promoter')
            ->latest()
            ->paginate(20);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function create()
    {
        $promoters = User::where('role', 'promoter')->orderBy('name')->get();
        return view('admin.promo-codes.create', compact('promoters'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Promo kod yaradıldı.');
    }

    public function edit(PromoCode $promoCode)
    {
        $promoters = User::where('role', 'promoter')->orderBy('name')->get();
        return view('admin.promo-codes.edit', compact('promoCode', 'promoters'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $this->validateData($request, $promoCode->id);
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', false);

        $promoCode->update($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Promo kod yeniləndi.');
    }

    public function destroy(PromoCode $promoCode)
    {
        if ($promoCode->redemptions()->exists()) {
            return back()->with('error', 'İstifadə olunmuş promo kodu silmək olmaz. Onu deaktiv edin.');
        }

        $promoCode->delete();
        return back()->with('success', 'Promo kod silindi.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'             => ['required', 'string', 'max:50', 'alpha_dash',
                                   'unique:promo_codes,code' . ($ignoreId ? ',' . $ignoreId : '')],
            'promoter_id'      => ['required', 'exists:users,id'],
            'discount_type'    => ['required', 'in:percent,fixed'],
            'discount_value'   => ['required', 'numeric', 'min:0'],
            'commission_type'  => ['required', 'in:percent,fixed'],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date'],
        ]);
    }
}
