<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter',
                'price' => 29.99,
                'patient_limit' => 50,
                'sms_limit' => 200,
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price' => 79.99,
                'patient_limit' => 200,
                'sms_limit' => 1000,
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 149.99,
                'patient_limit' => null,
                'sms_limit' => null,
                'duration_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
