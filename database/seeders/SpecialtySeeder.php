<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            'Stomatoloq',
            'Ginekolog',
            'Terapevt',
            'Pediatr',
            'Cərrah',
            'Neyroloq',
            'Kardiloq',
            'Dərmatolog',
        ];

        foreach ($specialties as $name) {
            Specialty::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
