<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Industry;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JobPostingPracticeSeeder extends Seeder
{
    /** Reused from JobSeeder's own image set so nothing points at a file that doesn't exist in storage. */
    private const IMAGES = [
        'jobImages/softdev.png',
        'jobImages/accounting.jpg',
        'jobImages/networkenginerr.png',
        'jobImages/q1y0JaVz4IA7oWkm3LLuU0fpBABRjt2Duyz7nYhI.png',
        'jobImages/QWLRxAmwXfRiRceBILODnbhQl3g5Og8JZswxwBzj.jpg',
        'jobImages/HbcdSxhcwBpO4tn4u4qnMYo8EMVVbvq64x59ieE8.jpg',
    ];

    /**
     * Per industry: candidate job titles, the skills those roles look for,
     * and which programs are a sensible target. Keeps generated postings
     * coherent (a "Junior Accountant" role asking for Bookkeeping, targeting
     * BS Accountancy) instead of fully random title/skill/program combos.
     */
    private const TEMPLATES = [
        'Technology' => [
            'titles' => ['Backend Developer', 'QA Tester', 'Systems Analyst', 'UI/UX Designer', 'Mobile App Developer', 'Technical Support Engineer'],
            'skills' => ['PHP', 'Laravel', 'JavaScript', 'React', 'HTML & CSS', 'Git & GitHub', 'Network Administration'],
            'programs' => ['Bachelor of Science in Information Technology'],
        ],
        'Healthcare' => [
            'titles' => ['Medical Receptionist', 'Health Information Officer', 'Wellness Program Coordinator', 'Patient Care Assistant'],
            'skills' => ['Communication', 'Microsoft Excel', 'Psychological Assessment', 'Counseling'],
            'programs' => ['Bachelor of Science in Psychology', 'Bachelor of Science in Social Work'],
        ],
        'Business & Finance' => [
            'titles' => ['Junior Accountant', 'Financial Analyst', 'Sales Associate', 'Administrative Assistant', 'Payroll Officer', 'Brand Coordinator'],
            'skills' => ['Bookkeeping', 'Financial Auditing', 'Microsoft Excel', 'Financial Modeling', 'Digital Marketing', 'Content Writing', 'Recruitment & Selection'],
            'programs' => [
                'Bachelor of Science in Accountancy',
                'Bachelor of Science in Business Administration Major in Financial Management',
                'Bachelor of Science in Business Administration Major in Marketing Management',
                'Bachelor of Science in Business Administration Major in Human Resource Management',
            ],
        ],
        'Education' => [
            'titles' => ['Training Coordinator', 'Curriculum Assistant', 'Academic Tutor', 'Learning Support Specialist'],
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Communication'],
            'programs' => [
                'Bachelor of Early Childhood Education',
                'Bachelor of Secondary Education Major in English',
                'Bachelor of Secondary Education Major in Mathematics',
                'Bachelor of Secondary Education Major in Science',
                'Bachelor of Secondary Education Major in Social Studies',
                'Bachelor of Secondary Education Major in Filipino',
            ],
        ],
        'Government & Public Service' => [
            'titles' => ['Community Development Officer', 'Policy Research Assistant', 'Case Worker', 'Public Affairs Assistant'],
            'skills' => ['Policy Analysis', 'Public Administration', 'Community Organizing', 'Case Management'],
            'programs' => ['Bachelor of Public Administration', 'Bachelor of Science in Social Work'],
        ],
        'Engineering & Construction' => [
            'titles' => ['Junior Civil Engineer', 'Drafting Technician', 'Site Inspector', 'Electrical Maintenance Technician'],
            'skills' => ['AutoCAD', 'Structural Analysis', 'Project Estimation', 'Circuit Design'],
            'programs' => ['Bachelor of Science in Civil Engineering', 'Bachelor of Science in Electrical Engineering'],
        ],
    ];

    /**
     * JobSeeder only posts 10 jobs across the original 5 employers. Adds a
     * broader spread across every employer (including
     * EmployerPracticeSeeder's) — must run after both EmployerSeeder and
     * EmployerPracticeSeeder. Closing dates are deliberately varied
     * (already past / closing this week / months out) so "active",
     * "expiring soon", and "closed" all have real rows on the employer
     * dashboard and job board.
     */
    public function run(): void
    {
        $employers = Employer::with('user')->get();
        if ($employers->isEmpty()) {
            return;
        }

        $existingTitles = JobPosting::pluck('job_posting_title')->all();

        for ($i = 1; $i <= 24; $i++) {
            $employer = $employers->random();
            $industryName = Industry::find($employer->industry_id)?->industry_name;
            $template = self::TEMPLATES[$industryName] ?? self::TEMPLATES[array_rand(self::TEMPLATES)];

            $title = $template['titles'][array_rand($template['titles'])];
            // Keep titles unique per company so two identical postings from
            // the same employer don't show up back to back on the board.
            $fullTitle = $title;
            $suffixLetter = 1;
            while (in_array($employer->employer_company_name . '|' . $fullTitle, $existingTitles, true)) {
                $fullTitle = $title . ' ' . ++$suffixLetter;
            }
            $existingTitles[] = $employer->employer_company_name . '|' . $fullTitle;

            $programIds = Program::whereIn('program_name', $template['programs'])->pluck('program_id');
            $skillNames = collect($template['skills'])->shuffle()->take(fake()->numberBetween(2, 4));

            // ~15% already closed, ~20% closing within a week, the rest open for a while yet.
            $closingRoll = fake()->numberBetween(1, 100);
            $closingDate = match (true) {
                $closingRoll <= 15 => Carbon::now()->subDays(fake()->numberBetween(1, 20)),
                $closingRoll <= 35 => Carbon::now()->addDays(fake()->numberBetween(1, 7)),
                default => Carbon::now()->addDays(fake()->numberBetween(15, 90)),
            };

            $job = JobPosting::create([
                'job_posting_title' => $fullTitle,
                'job_posting_company' => $employer->employer_company_name,
                'job_posting_address' => fake()->randomElement(['Valenzuela City', 'Quezon City', 'Caloocan City', 'Manila', 'Malabon City']) . ', Metro Manila',
                'job_posting_employment_type' => fake()->randomElement(['Full-Time', 'Full-Time', 'Part-Time', 'Freelance']),
                'job_posting_setup' => fake()->randomElement(['On-Site', 'Remote', 'Hybrid']),
                'job_posting_description' => "We are looking for a {$fullTitle} to join {$employer->employer_company_name}. "
                    . fake()->paragraph(3),
                'job_posting_date' => Carbon::now()->subDays(fake()->numberBetween(0, 45)),
                'job_closing_date' => $closingDate,
                'hiring_limit' => fake()->numberBetween(1, 4),
                'job_posting_image' => self::IMAGES[array_rand(self::IMAGES)],
                // Mostly approved so the board has content; a handful stay
                // pending so the admin approval queue isn't always empty.
                'job_approved' => fake()->boolean(80),
                'industry_id' => $employer->industry_id,
                'user_id' => $employer->user_id,
            ]);

            $job->programs()->attach($programIds);

            foreach ($skillNames as $skillName) {
                $skillId = Skill::where('skill_name', $skillName)->value('skill_id');
                if ($skillId) {
                    $job->skills()->attach($skillId, ['is_required' => true]);
                }
            }
        }
    }
}
