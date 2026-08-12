<?php

namespace Database\Seeders;

use App\Models\Industry;
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
        $employers = [
            [
                'user_first_name' => 'Charles',
                'user_last_name' => 'Tarog',
                'user_email' => 'employer@example.com',
                'company' => 'Tech Solutions Inc.',
                'industry' => 'Technology',
            ],
            [
                'user_first_name' => 'Dana',
                'user_last_name' => 'Reyes',
                'user_email' => 'employer2@example.com',
                'company' => 'MedCare Health Group',
                'industry' => 'Healthcare',
            ],
            [
                'user_first_name' => 'Miguel',
                'user_last_name' => 'Santos',
                'user_email' => 'employer3@example.com',
                'company' => 'BrightPath Learning Center',
                'industry' => 'Education',
            ],
            [
                'user_first_name' => 'Patricia',
                'user_last_name' => 'Gomez',
                'user_email' => 'employer4@example.com',
                'company' => 'Prime Financial Corp',
                'industry' => 'Business & Finance',
            ],
            [
                'user_first_name' => 'Rafael',
                'user_last_name' => 'Cruz',
                'user_email' => 'employer5@example.com',
                'company' => 'BuildRight Construction',
                'industry' => 'Engineering & Construction',
            ],
        ];

        foreach ($employers as $data) {
            $industryId = Industry::where('industry_name', $data['industry'])->value('industry_id');

            $user = User::create([
                'user_email' => $data['user_email'],
                'user_password' => Hash::make('employer123'),
                'user_first_name' => $data['user_first_name'],
                'user_last_name' => $data['user_last_name'],
                'user_role' => 'employer',
                'user_active' => true,
            ]);

            $user->employer()->create([
                'employer_company_name' => $data['company'],
                'industry_id' => $industryId,
                'employer_approved' => true,
                'employer_company_document' => 'companyDocuments/' . str($data['company'])->slug() . '.pdf',
            ]);
        }
    }
}
