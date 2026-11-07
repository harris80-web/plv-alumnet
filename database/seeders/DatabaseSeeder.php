<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            IndustrySeeder::class,
            ProgramSeeder::class,
            SkillsSeeder::class, // before RealisticSeeder — alumni and job posts attach skills by name
            // Every account and all activity: 2 super admins, 15 admins, 1000 alumni,
            // 100 employers, job posts, applications, notices, messages, chatbot tickets…
            // (see RealisticSeeder). Replaces the older fixed-data seeders (AlumnusSeeder,
            // EmployerSeeder, JobSeeder, CohortSeeder, …), which stay in this folder for
            // reference but no longer run.
            RealisticSeeder::class,
            FaqSeeder::class, // after RealisticSeeder — attributes each FAQ to a staff creator
        ]);
    }
}
