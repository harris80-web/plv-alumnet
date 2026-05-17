<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        JobPosting::create([
            'job_posting_title' => 'Full stack developer',
            'job_posting_company' => 'Tech Solutions Inc.',
            'job_posting_address' => 'Maysan',
            'job_posting_employment_type' => 'Full-Time',
            'job_posting_setup' => 'On-Site',
            'job_posting_description' => 'We are looking for a skilled full stack developer to join our team.',
            'job_posting_date' => Carbon::now(),
            'job_closing_date' => Carbon::now()->addDays(30),
            'job_posting_image' => 'jobImages\nIvgUfSD6vweuGvvz4TZnC4wssEqHIZtzljzoeCY.png',
            'job_approved' => true,
            'user_id' => 3,
        ]);

        JobPosting::create([
            'job_posting_title' => 'Front-end developer',
            'job_posting_company' => 'Tech Solutions Inc.',
            'job_posting_address' => 'Karuhatan',
            'job_posting_employment_type' => 'Part-Time',
            'job_posting_setup' => 'Remote',
            'job_posting_description' => 'We are looking for a skilled front-end developer to join our team.',
            'job_posting_date' => Carbon::now(),
            'job_closing_date' => Carbon::now()->addDays(30),
            'job_posting_image' => 'jobImages\nIvgUfSD6vweuGvvz4TZnC4wssEqHIZtzljzoeCY.png',
            'job_approved' => false,
            'user_id' => 3,
        ]);
    }
}
