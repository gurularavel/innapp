<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'super_admin')
            ->latest()
            ->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'surname'   => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'required|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['role']      = 'super_admin';
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin yaradıldı.');
    }

    public function edit(User $admin)
    {
        abort_unless($admin->isAdmin(), 404);
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        abort_unless($admin->isAdmin(), 404);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'surname'   => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $admin->id,
            'phone'     => 'nullable|string|max:20',
            'password'  => 'nullable|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $emailChanged = $admin->email !== $validated['email'];

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Öz hesabını deaktiv etməyə icazə vermə (kilidlənmənin qarşısını alır)
        if ($admin->id === Auth::id()) {
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->boolean('is_active', false);
        }

        if ($emailChanged) {
            $validated['remember_token'] = Str::random(60);
            DB::table('sessions')->where('user_id', $admin->id)->delete();
        }

        $admin->update($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin məlumatları yeniləndi.');
    }

    public function destroy(User $admin)
    {
        abort_unless($admin->isAdmin(), 404);

        if ($admin->id === Auth::id()) {
            return back()->with('error', 'Öz hesabınızı silə bilməzsiniz.');
        }

        if (User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Sistemdə ən azı bir admin qalmalıdır.');
        }

        $admin->delete();

        return back()->with('success', 'Admin silindi.');
    }
}
