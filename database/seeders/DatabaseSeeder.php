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
            AdminSeeder::class,
            JobSeeder::class, // after Employer/Industry/Program/Skills
            JobApplicationSeeder::class, // after Alumnus/Job
            AlumniPracticeSeeder::class, // after Program/Section/Industry/Skills/Job (uses approved jobs)
            AlumniYearbookSeeder::class, // after AlumnusSeeder + AlumniPracticeSeeder (needs every alumnus to exist)
            NoticeSeeder::class, // after AlumnusSeeder + AlumniPracticeSeeder (attaches interested alumni)
            MessageSeeder::class, // after AlumnusSeeder (needs alumni@example.com + a few named alumni)
            TestimonialSeeder::class,
            FaqSeeder::class, // after AdminSeeder/SuperAdminSeeder (attributes each FAQ to a staff creator)
            ChatbotSeeder::class, // after AlumnusSeeder/EmployerSeeder/AdminSeeder (needs named accounts + a claimable office)
            // You can add more seeders here later (e.g., UserSeeder)
        ]);
    }
}
