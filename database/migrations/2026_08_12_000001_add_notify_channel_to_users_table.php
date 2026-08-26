<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('notify_channel', ['sms', 'whatsapp', 'both'])
                ->default('sms')
                ->after('sms_copy_to_self')
                ->comment('Which channel(s) appointment and reminder messages are sent through');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notify_channel');
        });
    }
};
