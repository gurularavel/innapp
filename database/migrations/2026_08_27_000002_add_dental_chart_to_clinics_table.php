<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('dental_chart_enabled')->default(false)->after('holiday_greetings_enabled');
        });

        // The platform is generic, so the chart stays off by default — but turn it
        // on for clinics that already look dental so nobody has to hunt for a switch.
        $dentalClinicIds = DB::table('users')
            ->join('specialties', 'specialties.id', '=', 'users.specialty_id')
            ->whereNotNull('users.clinic_id')
            ->where(function ($q) {
                foreach (['stomat', 'diş', 'dis ', 'dent', 'ortodont', 'implantol'] as $needle) {
                    $q->orWhere('specialties.name', 'like', '%' . $needle . '%');
                }
            })
            ->distinct()
            ->pluck('users.clinic_id');

        if ($dentalClinicIds->isNotEmpty()) {
            DB::table('clinics')->whereIn('id', $dentalClinicIds)->update(['dental_chart_enabled' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('dental_chart_enabled');
        });
    }
};
