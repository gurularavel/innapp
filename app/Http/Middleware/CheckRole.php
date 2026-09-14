<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            // A signed-in user landing in another role's panel (back button,
            // old bookmark, URL autocomplete) is sent to their own panel
            // instead of a dead-end 403.
            if ($request->isMethod('GET') && !$request->expectsJson()) {
                return redirect($request->user()->homeUrl())
                    ->with('warning', 'Bu səhifəyə giriş icazəniz yoxdur.');
            }

            abort(403, 'Bu səhifəyə giriş icazəniz yoxdur.');
        }

        return $next($request);
    }
}
