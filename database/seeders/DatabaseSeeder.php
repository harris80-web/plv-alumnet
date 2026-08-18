<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            IndustrySeeder::class,
            ProgramSeeder::class,
            SectionSeeder::class,
            SkillsSeeder::class, // before Alumnus/Job — both attach skills by name
            SuperAdminSeeder::class,
            AlumnusSeeder::class,
            EmployerSeeder::class,
            EmployerPracticeSeeder::class, // after EmployerSeeder/IndustrySeeder — bulk employers for JobPostingPracticeSeeder to post under
            AdminSeeder::class,
            AdminPracticeSeeder::class, // after AdminSeeder — more than one admin for the cross-review split to exercise
            JobSeeder::class, // after Employer/Industry/Program/Skills
            JobPostingPracticeSeeder::class, // after JobSeeder/EmployerPracticeSeeder — bulk postings across every employer
            JobApplicationSeeder::class, // after Alumnus/Job
            AlumniPracticeSeeder::class, // after Program/Section/Industry/Skills/Job (uses approved jobs)
            AlumniPracticeSeeder2::class, // after AlumniPracticeSeeder — second wave, non-colliding emails, sees the larger job pool
            AlumniYearbookSeeder::class, // after every alumni seeder (needs every alumnus to exist)
            NoticeSeeder::class, // after every alumni seeder (attaches interested alumni)
            NoticePracticeSeeder::class, // after NoticeSeeder + every alumni seeder
            MessageSeeder::class, // after every alumni seeder (bulk pass scales with the full alumni pool)
            TestimonialSeeder::class,
            TestimonialPracticeSeeder::class, // after TestimonialSeeder + every alumni seeder
            FaqSeeder::class, // after AdminSeeder/SuperAdminSeeder (attributes each FAQ to a staff creator)
            ChatbotSeeder::class, // after AlumnusSeeder/EmployerSeeder/AdminSeeder (needs named accounts + a claimable office)
            // You can add more seeders here later (e.g., UserSeeder)
        ]);
    }
}
