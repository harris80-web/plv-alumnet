<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user=User::create([
            'user_email' => 'employer@example.com',
            'user_password' => Hash::make('employer123'),
            'user_first_name' => 'Charles',
            'user_last_name' => 'Tarog',
            'user_role' => 'employer',
            'user_active' => true,
        ]);
        $user->employer()->create([
            'employer_company_name' => 'Tech Solutions Inc.',
            'industry_id' => 1,
        ]);
    }
}
