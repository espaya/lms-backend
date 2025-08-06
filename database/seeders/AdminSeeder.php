<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'techdex',
            'email' => 'techdexgh@gmail.com',
            'password' => Hash::make('Magicmediaghana77@'),
            'role' => 'ADMIN',
            'privacy' => 1,
        ]);
    }
}
