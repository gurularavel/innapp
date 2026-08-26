<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->enum('channel', ['sms', 'whatsapp'])
                ->default('sms')
                ->after('type')
                ->comment('Delivery channel the message was sent through');

            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex(['channel', 'status']);
            $table->dropColumn('channel');
        });
    }
};
