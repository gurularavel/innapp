<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Greetings are addressed to a patient rather than to an appointment, and
     * they must go out exactly once. `reference` carries the occasion
     * ("birthday:2026-08-26", "holiday:12:2026") so a re-run of the scheduler
     * can tell what has already been sent instead of greeting twice.
     */
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('appointment_id')
                ->constrained()->nullOnDelete();
            $table->string('reference', 64)->nullable()->after('type')
                ->comment('Occasion key, unique per patient — guards against double sending');

            $table->index(['patient_id', 'reference']);
        });

        if ($this->isMysql()) {
            DB::statement("ALTER TABLE sms_logs MODIFY COLUMN type ENUM('appointment', 'reminder', 'birthday', 'holiday', 'custom') NOT NULL DEFAULT 'appointment'");
        }
    }

    public function down(): void
    {
        if ($this->isMysql()) {
            DB::table('sms_logs')->whereIn('type', ['birthday', 'holiday'])->update(['type' => 'custom']);
            DB::statement("ALTER TABLE sms_logs MODIFY COLUMN type ENUM('appointment', 'reminder', 'custom') NOT NULL DEFAULT 'appointment'");
        }

        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex(['patient_id', 'reference']);
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['patient_id', 'reference']);
        });
    }

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};
