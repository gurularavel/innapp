<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records now belong to the clinic, not to an individual member.
     * `doctor_id` keeps its meaning per table (assigned staff / author / creator)
     * but it must no longer own the row — removing a staff member cannot delete
     * the clinic's patients or appointment history.
     */
    private array $tables = [
        'patients',
        'treatment_types',
        'appointments',
        'patient_visits',
        'sms_logs',
        'subscription_payments',
        'doctor_subscriptions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->foreignId('clinic_id')->nullable()->after('id')
                    ->constrained('clinics')->cascadeOnDelete();
                $t->index(['clinic_id']);
            });

            if ($this->isMysql()) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['doctor_id']);
                });
            }

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('doctor_id')->nullable()->change();
            });

            if ($this->isMysql()) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('doctor_id')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['clinic_id']);
                $t->dropIndex(['clinic_id']);
                $t->dropColumn('clinic_id');
            });
        }
    }

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};
