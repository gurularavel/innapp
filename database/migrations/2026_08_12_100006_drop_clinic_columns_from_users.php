<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These belonged to the organisation, not the person — they now live on
     * `clinics` and were copied over by the previous migration.
     */
    private array $columns = [
        'muessise_adi',
        'muessise_unvani',
        'muessise_xerite',
        'muessise_xerite_code',
        'sms_appointment_template',
        'sms_reminder_template',
        'sms_copy_to_self',
        'notify_channel',
    ];

    public function up(): void
    {
        // The unique index on the map code must go first, otherwise SQLite
        // refuses to drop the column it points at.
        if (Schema::hasColumn('users', 'muessise_xerite_code')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropUnique('users_muessise_xerite_code_unique');
                } catch (\Throwable $e) {
                    // Index was never created on this connection — nothing to do.
                }
            });
        }

        Schema::table('users', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('muessise_adi', 100)->nullable();
            $table->string('muessise_unvani')->nullable();
            $table->string('muessise_xerite', 2000)->nullable();
            $table->string('muessise_xerite_code', 16)->nullable();
            $table->text('sms_appointment_template')->nullable();
            $table->text('sms_reminder_template')->nullable();
            $table->boolean('sms_copy_to_self')->default(false);
            $table->enum('notify_channel', ['sms', 'whatsapp', 'both'])->default('sms');
        });
    }
};
