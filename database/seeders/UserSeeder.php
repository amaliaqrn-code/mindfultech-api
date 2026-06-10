<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'ummul@gmail.com'
        ], [
            'name' => 'Ummul',
            'password' => Hash::make('123456')
        ]);
    }
}
