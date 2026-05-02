<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run()
    {
        Task::insert([
            [
                'user_id' => 1,
                'category_id' => 1,
                'title' => 'Belajar Laravel API',
                'description' => 'Bikin endpoint',
                'difficulty' => 'medium',
                'deadline' => now()->addDays(1),
                'is_completed' => false,
                'created_at' => now()
            ],
            [
                'user_id' => 1,
                'category_id' => 1,
                'title' => 'Nonton tutorial Flutter',
                'description' => null,
                'difficulty' => 'easy',
                'deadline' => now()->addDays(2),
                'is_completed' => false,
                'created_at' => now()
            ],
            [
                'user_id' => 1,
                'category_id' => 2,
                'title' => 'Kerjain project',
                'description' => 'Deadline dekat',
                'difficulty' => 'hard',
                'deadline' => now()->addHours(5),
                'is_completed' => false,
                'created_at' => now()
            ]
        ]);
    }
}
