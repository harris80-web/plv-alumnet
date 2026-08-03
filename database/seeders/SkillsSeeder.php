<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    /**
     * Spans every program in the `programs` table, not just BSIT, so the
     * skill search works for any alumnus regardless of course. Alumni can
     * still add new skills through the "+ Add as new skill" option in the
     * resume builder — this is just a sane starting set so the search box
     * isn't empty on day one.
     */
    public function run(): void
    {
        $skills = [
            // technical (BSIT / Engineering)
            ['skill_name' => 'PHP', 'skill_category' => 'technical'],
            ['skill_name' => 'Laravel', 'skill_category' => 'technical'],
            ['skill_name' => 'JavaScript', 'skill_category' => 'technical'],
            ['skill_name' => 'HTML & CSS', 'skill_category' => 'technical'],
            ['skill_name' => 'React', 'skill_category' => 'technical'],
            ['skill_name' => 'Python', 'skill_category' => 'technical'],
            ['skill_name' => 'Java', 'skill_category' => 'technical'],
            ['skill_name' => 'AutoCAD', 'skill_category' => 'technical'],
            ['skill_name' => 'Circuit Design', 'skill_category' => 'technical'],
            ['skill_name' => 'Structural Analysis', 'skill_category' => 'technical'],
            ['skill_name' => 'Network Administration', 'skill_category' => 'technical'],

            // tools
            ['skill_name' => 'MySQL', 'skill_category' => 'tool'],
            ['skill_name' => 'Figma', 'skill_category' => 'tool'],
            ['skill_name' => 'Microsoft Excel', 'skill_category' => 'tool'],
            ['skill_name' => 'Microsoft Word', 'skill_category' => 'tool'],
            ['skill_name' => 'QuickBooks', 'skill_category' => 'tool'],
            ['skill_name' => 'SPSS', 'skill_category' => 'tool'],
            ['skill_name' => 'Canva', 'skill_category' => 'tool'],
            ['skill_name' => 'Adobe Photoshop', 'skill_category' => 'tool'],
            ['skill_name' => 'Git & GitHub', 'skill_category' => 'tool'],

            // language
            ['skill_name' => 'English', 'skill_category' => 'language'],
            ['skill_name' => 'Filipino', 'skill_category' => 'language'],

            // soft skills — apply to every program
            ['skill_name' => 'Communication', 'skill_category' => 'soft'],
            ['skill_name' => 'Leadership', 'skill_category' => 'soft'],
            ['skill_name' => 'Time Management', 'skill_category' => 'soft'],
            ['skill_name' => 'Teamwork', 'skill_category' => 'soft'],
            ['skill_name' => 'Critical Thinking', 'skill_category' => 'soft'],
            ['skill_name' => 'Problem Solving', 'skill_category' => 'soft'],
            ['skill_name' => 'Adaptability', 'skill_category' => 'soft'],

            // domain — non-IT program-specific competencies
            ['skill_name' => 'Lesson Planning', 'skill_category' => 'domain'],           // BSEd / BECEd
            ['skill_name' => 'Classroom Management', 'skill_category' => 'domain'],      // BSEd / BECEd
            ['skill_name' => 'Curriculum Development', 'skill_category' => 'domain'],    // BSEd
            ['skill_name' => 'Financial Auditing', 'skill_category' => 'domain'],        // BSA
            ['skill_name' => 'Tax Preparation', 'skill_category' => 'domain'],           // BSA
            ['skill_name' => 'Bookkeeping', 'skill_category' => 'domain'],               // BSA
            ['skill_name' => 'Financial Modeling', 'skill_category' => 'domain'],        // BSBA-FM
            ['skill_name' => 'Recruitment & Selection', 'skill_category' => 'domain'],   // BSBA-HRM
            ['skill_name' => 'Payroll Management', 'skill_category' => 'domain'],        // BSBA-HRM
            ['skill_name' => 'Digital Marketing', 'skill_category' => 'domain'],         // BSBA-MM
            ['skill_name' => 'Market Research', 'skill_category' => 'domain'],           // BSBA-MM
            ['skill_name' => 'Case Management', 'skill_category' => 'domain'],           // Social Work
            ['skill_name' => 'Community Organizing', 'skill_category' => 'domain'],      // Social Work / Public Admin
            ['skill_name' => 'Policy Analysis', 'skill_category' => 'domain'],           // BPA
            ['skill_name' => 'Public Administration', 'skill_category' => 'domain'],     // BPA
            ['skill_name' => 'Psychological Assessment', 'skill_category' => 'domain'],  // Psychology
            ['skill_name' => 'Counseling', 'skill_category' => 'domain'],                // Psychology
            ['skill_name' => 'Behavioral Analysis', 'skill_category' => 'domain'],       // Psychology
            ['skill_name' => 'Content Writing', 'skill_category' => 'domain'],           // Communication
            ['skill_name' => 'Public Speaking', 'skill_category' => 'domain'],           // Communication
            ['skill_name' => 'Media Production', 'skill_category' => 'domain'],          // Communication
            ['skill_name' => 'Project Estimation', 'skill_category' => 'domain'],        // Civil Engineering
            ['skill_name' => 'Electrical Systems Design', 'skill_category' => 'domain'], // Electrical Engineering
        ];

        foreach ($skills as $skill) {
            DB::table('skills')->updateOrInsert(
                ['skill_name' => $skill['skill_name']],
                array_merge($skill, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}