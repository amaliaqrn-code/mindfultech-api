<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Hanya CategorySeeder sebagai referensi default
        // Streak, JourneyProgress, Categories dibuat otomatis via User::booted()
        // Tidak ada data dummy user/task/focus session
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
