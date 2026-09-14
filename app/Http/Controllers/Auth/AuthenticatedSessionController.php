<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DemoController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // The "intended" URL is written by the auth middleware whenever a
        // guest hits a protected page — including a stale /promoter/... or
        // /admin/... link the browser re-opened after a *different* account
        // logged out. Honour it only when it belongs to this user's own panel,
        // otherwise the login would land on a 403.
        $intended = $request->session()->pull('url.intended');

        return redirect($user->ownsUrl($intended) ? $intended : $user->homeUrl());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user?->is_demo) {
            DemoController::deleteDemo($user);
        }

        return redirect('/');
    }
}
