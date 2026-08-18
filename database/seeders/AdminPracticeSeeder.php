<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminPracticeSeeder extends Seeder
{
    /**
     * AdminSeeder only ever creates one admin account. With just one, the
     * cross-review split in JobPostingController::showJobManagement() (each
     * staff role reviews the other's postings) and the Admins list in User
     * Management have nothing real to show beyond a single row. Adds a
     * couple more — skips any email that already exists so this is safe to
     * re-run.
     */
    public function run(): void
    {
        $admins = [
            ['first' => 'Ramon', 'last' => 'Villareal', 'email' => 'admin2@example.com', 'address' => '45 Rizal Avenue, Valenzuela City'],
            ['first' => 'Cecilia', 'last' => 'Navarette', 'email' => 'admin3@example.com', 'address' => '78 Bonifacio Street, Valenzuela City'],
        ];

        foreach ($admins as $data) {
            if (User::where('user_email', $data['email'])->exists()) {
                continue;
            }

            $user = User::create([
                'user_email' => $data['email'],
                'user_password' => Hash::make('password123'),
                'user_first_name' => $data['first'],
                'user_last_name' => $data['last'],
                'user_role' => 'admin',
                'user_active' => true,
            ]);

            $user->office()->create([
                'office_address' => $data['address'],
            ]);
        }
    }
}
