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
            TestimonialSeeder::class,
            // You can add more seeders here later (e.g., UserSeeder)
        ]);
    }
}
