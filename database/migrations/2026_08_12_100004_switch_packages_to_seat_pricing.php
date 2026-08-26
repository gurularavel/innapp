<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pricing is now per staff seat: a clinic pays `price_per_seat` for every
     * account it has. Messaging is unlimited, so the SMS quota disappears.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price_per_seat', 8, 2)->default(20)->after('name');
            $table->unsignedInteger('min_seats')->default(1)->after('price_per_seat');
            $table->unsignedInteger('max_seats')->nullable()->after('min_seats')
                ->comment('null = unlimited seats');
            $table->text('description')->nullable()->after('max_seats');
        });

        // Carry the old flat monthly price over as the per-seat price.
        DB::table('packages')->update(['price_per_seat' => DB::raw('price')]);

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['price', 'sms_limit']);
        });

        Schema::table('doctor_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('seats')->default(1)->after('package_id')
                ->comment('Paid staff seats for this billing period');
            $table->decimal('price_per_seat', 8, 2)->default(0)->after('seats')
                ->comment('Locked in at purchase time');
            $table->dropColumn('sms_used');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0)->after('name');
            $table->unsignedInteger('sms_limit')->nullable();
        });

        DB::table('packages')->update(['price' => DB::raw('price_per_seat')]);

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['price_per_seat', 'min_seats', 'max_seats', 'description']);
        });

        Schema::table('doctor_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('sms_used')->default(0);
            $table->dropColumn(['seats', 'price_per_seat']);
        });
    }
};
