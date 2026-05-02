<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::insert([
            [
                'user_id' => 1,
                'name' => 'Belajar',
                'created_at' => now()
            ],
            [
                'user_id' => 1,
                'name' => 'Kerja',
                'created_at' => now()
            ],
            [
                'user_id' => 1,
                'name' => 'Pribadi',
                'created_at' => now()
            ]
        ]);
    }
}
