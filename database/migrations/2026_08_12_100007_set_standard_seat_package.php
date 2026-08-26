<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One standard plan: 20 AZN per staff seat, unlimited messaging and patients.
     *
     * Old tiered packages are deactivated rather than deleted — subscriptions
     * still point at them and must keep their history.
     */
    public function up(): void
    {
        $standard = DB::table('packages')->where('name', 'Standart')->first();

        $attributes = [
            'name'           => 'Standart',
            'price_per_seat' => 20.00,
            'min_seats'      => 1,
            'max_seats'      => null,
            'patient_limit'  => null,
            'duration_days'  => 30,
            'description'    => 'Hər əməkdaş üçün 20 ₼ / ay. Limitsiz müştəri, limitsiz SMS və WhatsApp.',
            'is_active'      => true,
            'updated_at'     => now(),
        ];

        if ($standard) {
            DB::table('packages')->where('id', $standard->id)->update($attributes);
            $standardId = $standard->id;
        } else {
            $standardId = DB::table('packages')->insertGetId($attributes + ['created_at' => now()]);
        }

        DB::table('packages')->where('id', '!=', $standardId)->update([
            'is_active'  => false,
            'updated_at' => now(),
        ]);

        // Existing subscriptions were bought as a single-person plan.
        DB::table('doctor_subscriptions')
            ->where('price_per_seat', 0)
            ->update(['seats' => 1, 'price_per_seat' => 20.00]);
    }

    public function down(): void
    {
        DB::table('packages')->update(['is_active' => true]);
    }
};
