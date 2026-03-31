<?php

namespace App\Http\Middleware;

use App\Http\Controllers\DemoController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDemoExpiry
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_demo && $user->demo_expires_at->isPast()) {
            $userId = $user->id;
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            DemoController::deleteDemo(\App\Models\User::find($userId));

            return redirect()->route('home')
                ->with('demo_expired', 'Demo müddəti başa çatdı. Bütün məlumatlar silindi. Qeydiyyatdan keçərək öz hesabınızı yaradın.');
        }

        return $next($request);
    }
}
