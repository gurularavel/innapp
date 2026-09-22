<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deactivation must take effect immediately, not at the next login.
 *
 * `is_active` is checked by LoginRequest when a session starts; this repeats
 * the check on every request so an owner or admin who switches an account off
 * (or an admin who closes a whole clinic) really locks it out — including a
 * session kept alive by "remember me".
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->is_active || ($user->clinic && ! $user->clinic->is_active))) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Hesabınız deaktiv edilib.'], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Hesabınız deaktiv edilib. Zəhmət olmasa administratora müraciət edin.']);
        }

        return $next($request);
    }
}
