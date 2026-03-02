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

        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return redirect()->route('doctor.dashboard')
                ->with('warning', 'Aktiv abunəliyiniz yoxdur. Zəhmət olmasa Super Admin ilə əlaqə saxlayın.');
        }

        return $next($request);
    }
}
