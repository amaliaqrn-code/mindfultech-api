<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FocusSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\FocusSession::create([
            'user_id' => 1,
            'task_id' => 1,
            'duration' => 25,
            'break_duration' => 5,
            'status' => 'finished',
            'started_at' => now()->subMinutes(30),
            'ended_at' => now()
        ]);
    }
}
