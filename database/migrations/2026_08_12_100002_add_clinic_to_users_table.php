<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')
                ->constrained('clinics')->nullOnDelete();

            // Only members with a calendar can be assigned appointments.
            // Receptionists and managers are staff too — every account is a paid seat.
            $table->boolean('takes_appointments')->default(true)->after('specialty_id');
            $table->string('job_title', 100)->nullable()->after('takes_appointments');

            $table->index(['clinic_id', 'is_active']);
        });

        // 'owner' and 'receptionist' join the existing platform roles.
        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'owner', 'doctor', 'receptionist', 'promoter') NOT NULL DEFAULT 'doctor'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['clinic_id', 'is_active']);
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['clinic_id', 'takes_appointments', 'job_title']);
        });

        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'doctor', 'promoter') NOT NULL DEFAULT 'doctor'");
        }
    }

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};
