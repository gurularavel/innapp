<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        // Pricing is per staff seat — a solo specialist is simply one seat.
        Package::updateOrCreate(
            ['name' => 'Standart'],
            [
                'price_per_seat' => 20.00,
                'min_seats'      => 1,
                'max_seats'      => null,
                'patient_limit'  => null,
                'duration_days'  => 30,
                'description'    => 'Hər əməkdaş üçün 20 ₼ / ay. Limitsiz müştəri, limitsiz SMS və WhatsApp.',
                'is_active'      => true,
            ]
        );
    }
}
