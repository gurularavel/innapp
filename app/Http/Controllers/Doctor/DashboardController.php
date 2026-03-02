<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = Auth::user();
        $subscription = $doctor->activeSubscription()->with('package')->first();

        $stats = [
            'total_patients' => $doctor->patients()->count(),
            'today_appointments' => $doctor->appointments()
                ->whereDate('scheduled_at', today())
                ->count(),
            'pending_appointments' => $doctor->appointments()
                ->where('status', 'pending')
                ->count(),
            'this_month_appointments' => $doctor->appointments()
                ->whereMonth('scheduled_at', now()->month)
                ->whereYear('scheduled_at', now()->year)
                ->count(),
        ];

        $todayAppointments = $doctor->appointments()
            ->with('patient', 'treatmentType')
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $upcomingAppointments = $doctor->appointments()
            ->with('patient', 'treatmentType')
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return view('doctor.dashboard', compact('stats', 'subscription', 'todayAppointments', 'upcomingAppointments'));
    }
}
