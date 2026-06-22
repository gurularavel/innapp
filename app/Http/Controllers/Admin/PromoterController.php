<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PromoterController extends Controller
{
    public function index()
    {
        $promoters = User::where('role', 'promoter')
            ->withCount('promoCodes')
            ->latest()
            ->paginate(15);

        return view('admin.promoters.index', compact('promoters'));
    }

    public function create()
    {
        return view('admin.promoters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'surname'  => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['role']      = 'promoter';
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.promoters.index')
            ->with('success', 'Promotor yaradıldı.');
    }

    public function show(User $promoter)
    {
        abort_unless($promoter->isPromoter(), 404);

        $balances = $promoter->commissionBalances();
        $codes    = $promoter->promoCodes()->withCount('redemptions')->get();
        $redemptions = $promoter->redemptions()
            ->with(['customer', 'promoCode', 'payment.package'])
            ->latest()
            ->limit(50)
            ->get();
        $payouts = $promoter->payouts()->latest()->get();

        return view('admin.promoters.show', compact('promoter', 'balances', 'codes', 'redemptions', 'payouts'));
    }

    public function edit(User $promoter)
    {
        abort_unless($promoter->isPromoter(), 404);
        return view('admin.promoters.edit', compact('promoter'));
    }

    public function update(Request $request, User $promoter)
    {
        abort_unless($promoter->isPromoter(), 404);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'surname'   => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $promoter->id,
            'phone'     => 'nullable|string|max:20',
            'password'  => 'nullable|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $emailChanged = $promoter->email !== $validated['email'];

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', false);

        if ($emailChanged) {
            $validated['remember_token'] = Str::random(60);
            DB::table('sessions')->where('user_id', $promoter->id)->delete();
        }

        $promoter->update($validated);

        return redirect()->route('admin.promoters.index')
            ->with('success', 'Promotor məlumatları yeniləndi.');
    }

    public function destroy(User $promoter)
    {
        abort_unless($promoter->isPromoter(), 404);
        $promoter->delete();
        return back()->with('success', 'Promotor silindi.');
    }
}
