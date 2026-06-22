<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'doctor') {
            return $next($request);
        }

        // Demo users bypass subscription check
        if ($user->is_demo) {
            return $next($request);
        }

        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return redirect()->route('panel.subscription.index')
                ->with('warning', 'Bu bölmədən istifadə üçün aktiv abunəlik lazımdır. Aşağıdan uyğun paketi seçib ödəniş edin.');
        }

        return $next($request);
    }
}
