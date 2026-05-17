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
        //
         $user=User::create([
            'user_email' => 'admin@example.com',
            'user_password' => Hash::make('password123'),
            'user_first_name' => 'Niña',
            'user_last_name' => 'Dela Cruz',
            'user_role' => 'admin',
            'user_active' => true,
        ]);
        $user->office()->create([
            'office_address' => '123 Main St, City, Country',
        ]);
    }
}
