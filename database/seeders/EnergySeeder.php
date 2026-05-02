<?php

namespace Database\Seeders;

use App\Models\EnergyLog;
use Illuminate\Database\Seeder;

class EnergySeeder extends Seeder
{
    public function run()
    {
        EnergyLog::create([
            'user_id' => 1,
            'energy_level' => 'medium',
            'created_at' => now()
        ]);
    }
}
