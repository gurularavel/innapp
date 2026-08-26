<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $clinic = $user->clinic;

        // Owners and receptionists run the front desk, so they see the whole
        // clinic; a specialist sees their own day.
        $wholeClinic = $user->isOwner() || $user->isReceptionist();

        $appointments = $wholeClinic
            ? $user->clinicAppointments()
            : $user->appointments();

        $subscription = $clinic?->activeSubscription()->with('package')->first();

        $stats = [
            'total_patients' => $user->patients()->count(),
            'today_appointments' => (clone $appointments)->whereDate('scheduled_at', today())->count(),
            'pending_appointments' => (clone $appointments)->where('status', 'pending')->count(),
            'this_month_appointments' => (clone $appointments)
                ->whereMonth('scheduled_at', now()->month)
                ->whereYear('scheduled_at', now()->year)
                ->count(),
            'staff_count' => $clinic?->usedSeats() ?? 1,
        ];

        $todayAppointments = (clone $appointments)
            ->with('patient', 'treatmentType', 'doctor')
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $upcomingAppointments = (clone $appointments)
            ->with('patient', 'treatmentType', 'doctor')
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return view('doctor.dashboard', compact(
            'stats',
            'subscription',
            'todayAppointments',
            'upcomingAppointments',
            'clinic',
            'wholeClinic'
        ));
    }
}
