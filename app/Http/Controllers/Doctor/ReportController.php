<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function revenue()
    {
        $doctor = Auth::user();
        $now    = Carbon::now();

        // ── Summary cards ──────────────────────────────────────────────
        $thisWeekRevenue  = $this->base($doctor->id)
            ->where('appointments.scheduled_at', '>=', (clone $now)->startOfWeek())
            ->sum('treatment_types.price');

        $thisMonthRevenue = $this->base($doctor->id)
            ->whereYear('appointments.scheduled_at',  $now->year)
            ->whereMonth('appointments.scheduled_at', $now->month)
            ->sum('treatment_types.price');

        $thisYearRevenue  = $this->base($doctor->id)
            ->whereYear('appointments.scheduled_at', $now->year)
            ->sum('treatment_types.price');

        $totalRevenue     = $this->base($doctor->id)
            ->sum('treatment_types.price');

        $completedCount   = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->count();

        $avgRevenue = $completedCount > 0 ? $totalRevenue / $completedCount : 0;

        // ── Weekly (last 7 days) ───────────────────────────────────────
        $weeklyRaw = $this->base($doctor->id)
            ->where('appointments.scheduled_at', '>=', (clone $now)->subDays(6)->startOfDay())
            ->selectRaw('DATE(appointments.scheduled_at) as period, SUM(treatment_types.price) as revenue, COUNT(*) as cnt')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $weeklyLabels = $weeklyRevenues = $weeklyCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date           = (clone $now)->subDays($i)->format('Y-m-d');
            $weeklyLabels[] = (clone $now)->subDays($i)->format('d.m');
            $weeklyRevenues[] = (float) ($weeklyRaw[$date]->revenue ?? 0);
            $weeklyCounts[]   = (int)   ($weeklyRaw[$date]->cnt     ?? 0);
        }

        // ── Monthly (year selector) ────────────────────────────────────
        $monthlyYear = (int) request('year', $now->year);
        $monthlyRaw  = $this->base($doctor->id)
            ->whereYear('appointments.scheduled_at', $monthlyYear)
            ->selectRaw('MONTH(appointments.scheduled_at) as period, SUM(treatment_types.price) as revenue, COUNT(*) as cnt')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $azMonths      = ['', 'Yanvar','Fevral','Mart','Aprel','May','İyun','İyul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'];
        $monthlyLabels = $monthlyRevenues = $monthlyCounts = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[]   = $azMonths[$m];
            $monthlyRevenues[] = (float) ($monthlyRaw[$m]->revenue ?? 0);
            $monthlyCounts[]   = (int)   ($monthlyRaw[$m]->cnt     ?? 0);
        }

        // ── Annual (last 5 years) ──────────────────────────────────────
        $annualRaw = $this->base($doctor->id)
            ->where('appointments.scheduled_at', '>=', (clone $now)->subYears(4)->startOfYear())
            ->selectRaw('YEAR(appointments.scheduled_at) as period, SUM(treatment_types.price) as revenue, COUNT(*) as cnt')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $annualLabels = $annualRevenues = $annualCounts = [];
        for ($y = $now->year - 4; $y <= $now->year; $y++) {
            $annualLabels[]   = (string) $y;
            $annualRevenues[] = (float) ($annualRaw[$y]->revenue ?? 0);
            $annualCounts[]   = (int)   ($annualRaw[$y]->cnt     ?? 0);
        }

        // ── Breakdown by service type ──────────────────────────────────
        $byService = $this->base($doctor->id)
            ->selectRaw('treatment_types.name, treatment_types.color, SUM(treatment_types.price) as revenue, COUNT(*) as cnt')
            ->groupBy('treatment_types.id', 'treatment_types.name', 'treatment_types.color')
            ->orderByDesc('revenue')
            ->get();

        // ── Available years for monthly picker ─────────────────────────
        $availableYears = Appointment::where('appointments.doctor_id', $doctor->id)
            ->where('appointments.status', 'completed')
            ->join('treatment_types', 'appointments.treatment_type_id', '=', 'treatment_types.id')
            ->selectRaw('YEAR(appointments.scheduled_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$now->year];
        }

        return view('doctor.reports.revenue', compact(
            'thisWeekRevenue', 'thisMonthRevenue', 'thisYearRevenue', 'totalRevenue',
            'completedCount', 'avgRevenue',
            'weeklyLabels',   'weeklyRevenues',   'weeklyCounts',
            'monthlyLabels',  'monthlyRevenues',  'monthlyCounts',  'monthlyYear',
            'annualLabels',   'annualRevenues',   'annualCounts',
            'byService',      'availableYears'
        ));
    }

    // Returns base query: doctor's completed appointments joined with treatment_types
    private function base(int $doctorId)
    {
        return Appointment::where('appointments.doctor_id', $doctorId)
            ->where('appointments.status', 'completed')
            ->join('treatment_types', 'appointments.treatment_type_id', '=', 'treatment_types.id');
    }
}
