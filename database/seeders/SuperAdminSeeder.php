<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;




class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user=User::create([
            'user_email' => 'superadmin@example.com',
            'user_password' => Hash::make('password123'),
            'user_first_name' => 'Harris',
            'user_last_name' => 'Cruz',
            'user_role' => 'super_admin',
            'user_active' => true,
        ]);
        $user->office()->create([
            'office_address' => '123 Main St, City, Country',
        ]);

    }
}
