<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlumnusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user=User::create([
            'user_email' => 'alumni@example.com',
            'user_password' => Hash::make('alumni123'),
            'user_first_name' => 'Ryza',
            'user_last_name' => 'Ison',
            'user_role' => 'alumni',
            'user_active' => true,
        ]);
        $user->alumnus()->create([
            'program_id' => 9,
            'alumnus_batch' => 2023,
            'section_id' => 2,
        ]);
    }
}
