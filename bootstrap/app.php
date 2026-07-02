<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckDemoExpiry::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sessiya müddəti bitdikdə (419) xəta səhifəsi əvəzinə ana səhifəyə yönləndir
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sessiyanın müddəti bitdi. Səhifəni yeniləyin.'], 419);
            }
            return redirect()->route('home')
                ->with('error', 'Sessiyanın müddəti bitdi. Zəhmət olmasa yenidən daxil olun.');
        });
    })->create();
