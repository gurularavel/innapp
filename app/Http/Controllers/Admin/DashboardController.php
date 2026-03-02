<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorSubscription;
use App\Models\Package;
use App\Models\Patient;
use App\Models\SmsLog;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_doctors' => User::where('role', 'doctor')->count(),
            'active_doctors' => User::where('role', 'doctor')->where('is_active', true)->count(),
            'total_patients' => Patient::count(),
            'total_appointments' => Appointment::count(),
            'today_appointments' => Appointment::whereDate('scheduled_at', today())->count(),
            'active_subscriptions' => DoctorSubscription::where('is_active', true)
                ->where('expires_at', '>=', now()->toDateString())
                ->count(),
            'total_sms' => SmsLog::where('status', 'sent')->count(),
            'total_packages' => Package::where('is_active', true)->count(),
        ];

        $recentDoctors = User::where('role', 'doctor')
            ->with('specialty', 'activeSubscription.package')
            ->latest()
            ->take(5)
            ->get();

        $expiringSubscriptions = DoctorSubscription::with('doctor', 'package')
            ->where('is_active', true)
            ->where('expires_at', '>=', now()->toDateString())
            ->where('expires_at', '<=', now()->addDays(7)->toDateString())
            ->get();

        return view('admin.dashboard', compact('stats', 'recentDoctors', 'expiringSubscriptions'));
    }
}
