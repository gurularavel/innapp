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

        if (! $user || ! $user->isClinicMember()) {
            return $next($request);
        }

        // Demo users bypass the subscription check
        if ($user->is_demo) {
            return $next($request);
        }

        $clinic       = $user->clinic;
        $subscription = $clinic?->activeSubscription;

        if (! $subscription) {
            return $this->blocked(
                $user,
                'Bu bölmədən istifadə üçün aktiv abunəlik lazımdır. Aşağıdan yer sayını seçib ödəniş edin.'
            );
        }

        // The clinic hired past what it pays for — billing must be corrected first.
        if ($subscription->seatsExceeded()) {
            return $this->blocked(
                $user,
                sprintf(
                    'Klinikanızda %d aktiv hesab var, abunəniz isə %d yer əhatə edir. Davam etmək üçün yer sayını artırın və ya artıq hesabları deaktiv edin.',
                    $subscription->used_seats,
                    $subscription->seats
                )
            );
        }

        return $next($request);
    }

    /**
     * Only the owner can fix billing, so everyone else is told who to ask
     * instead of being sent to a page they cannot act on.
     */
    private function blocked($user, string $ownerMessage): Response
    {
        if ($user->canManageClinic()) {
            return redirect()->route('panel.subscription.index')->with('warning', $ownerMessage);
        }

        return redirect()->route('panel.dashboard')
            ->with('warning', 'Klinikanızın abunəliyi aktiv deyil. Zəhmət olmasa klinika sahibi ilə əlaqə saxlayın.');
    }
}
