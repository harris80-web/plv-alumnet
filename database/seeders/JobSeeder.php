<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Industry;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Ten postings spread across the five seeded employers, each with a
     * hiring_limit, target programs and weighted required skills — enough
     * variety for JobApplicationSeeder to produce a realistic spread of
     * match scores, and for the hiring-limit / bulk-hire UI to have
     * something real to cap against.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Full Stack Developer',
                'company' => 'Tech Solutions Inc.',
                'address' => 'Maysan, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'We are looking for a skilled full stack developer to join our team, working across our Laravel backend and JavaScript front end.',
                'hiring_limit' => 2,
                'industry' => 'Technology',
                'programs' => ['Bachelor of Science in Information Technology'],
                'skills' => ['PHP' => 5, 'Laravel' => 5, 'JavaScript' => 3],
                'approved' => true,
                'image' => 'jobImages/softdev.png',
            ],
            [
                'title' => 'Front-End Developer',
                'company' => 'Tech Solutions Inc.',
                'address' => 'Karuhatan, Valenzuela City',
                'type' => 'Part-Time',
                'setup' => 'Remote',
                'description' => 'We are looking for a skilled front-end developer to join our team and help build responsive, accessible interfaces.',
                'hiring_limit' => 1,
                'industry' => 'Technology',
                'programs' => ['Bachelor of Science in Information Technology'],
                'skills' => ['JavaScript' => 5, 'HTML & CSS' => 4, 'React' => 3],
                'approved' => false,
                'image' => 'jobImages/FRFpqa86bUwmnhO1W2QjbEDgRIcV2A2GZxZYULdD.png',
            ],
            [
                'title' => 'IT Support Specialist',
                'company' => 'Tech Solutions Inc.',
                'address' => 'Malinta, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'Provide first-line technical support, maintain office network infrastructure, and assist staff with day-to-day IT issues.',
                'hiring_limit' => 3,
                'industry' => 'Technology',
                'programs' => ['Bachelor of Science in Information Technology', 'Bachelor of Science in Electrical Engineering'],
                'skills' => ['Network Administration' => 4, 'Microsoft Excel' => 2],
                'approved' => true,
                'image' => 'jobImages/networkenginerr.png',
            ],
            [
                'title' => 'Medical Records Clerk',
                'company' => 'MedCare Health Group',
                'address' => 'Marulas, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'Maintain patient records, coordinate with clinical staff, and ensure accurate documentation across departments.',
                'hiring_limit' => 1,
                'industry' => 'Healthcare',
                'programs' => ['Bachelor of Science in Psychology', 'Bachelor of Science in Social Work'],
                'skills' => ['Microsoft Excel' => 3, 'Communication' => 4],
                'approved' => true,
                'image' => 'jobImages/HbcdSxhcwBpO4tn4u4qnMYo8EMVVbvq64x59ieE8.jpg',
            ],
            [
                'title' => 'HR Generalist',
                'company' => 'MedCare Health Group',
                'address' => 'Marulas, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'Hybrid',
                'description' => 'Handle end-to-end recruitment, onboarding, and payroll support for a growing healthcare staff.',
                'hiring_limit' => 2,
                'industry' => 'Business & Finance',
                'programs' => ['Bachelor of Science in Business Administration Major in Human Resource Management'],
                'skills' => ['Recruitment & Selection' => 5, 'Payroll Management' => 3, 'Communication' => 3],
                'approved' => true,
                'image' => 'jobImages/accounting.jpg',
            ],
            [
                'title' => 'Elementary Teacher',
                'company' => 'BrightPath Learning Center',
                'address' => 'Pasolo, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'Plan and deliver engaging lessons for elementary learners and manage a positive classroom environment.',
                'hiring_limit' => 2,
                'industry' => 'Education',
                'programs' => ['Bachelor of Early Childhood Education', 'Bachelor of Secondary Education Major in Mathematics'],
                'skills' => ['Lesson Planning' => 5, 'Classroom Management' => 4],
                'approved' => true,
                'image' => 'jobImages/QWLRxAmwXfRiRceBILODnbhQl3g5Og8JZswxwBzj.jpg',
            ],
            [
                // Deliberately no target program (empty 'programs' below) —
                // JobMatchService gives full program credit to everyone in
                // that case, so this job is genuinely open to all 20 seeded
                // alumni. Used to build a 14-applicant pool for testing the
                // top-10 rank badge on the applicants page.
                'title' => 'Customer Success Associate',
                'company' => 'Tech Solutions Inc.',
                'address' => 'Maysan, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'Hybrid',
                'description' => 'Support customers across chat and email, troubleshoot common issues, and escalate as needed. Open to graduates of any program with strong communication skills.',
                'hiring_limit' => 4,
                'industry' => 'Technology',
                'programs' => [],
                'skills' => ['Communication' => 5, 'Teamwork' => 3, 'Critical Thinking' => 3],
                'approved' => true,
                'image' => 'jobImages/q1y0JaVz4IA7oWkm3LLuU0fpBABRjt2Duyz7nYhI.png',
            ],
            [
                'title' => 'Accounting Associate',
                'company' => 'Prime Financial Corp',
                'address' => 'Poblacion, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'Assist with financial audits, bookkeeping, and tax preparation for a growing portfolio of clients.',
                'hiring_limit' => 2,
                'industry' => 'Business & Finance',
                'programs' => ['Bachelor of Science in Accountancy'],
                'skills' => ['Financial Auditing' => 5, 'Bookkeeping' => 4, 'Microsoft Excel' => 3],
                'approved' => true,
                'image' => 'jobImages/accounting.jpg',
            ],
            [
                'title' => 'Marketing Coordinator',
                'company' => 'Prime Financial Corp',
                'address' => 'Poblacion, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'Hybrid',
                'description' => 'Plan and run digital marketing campaigns, coordinate with content creators, and track campaign performance.',
                'hiring_limit' => 1,
                'industry' => 'Business & Finance',
                'programs' => ['Bachelor of Science in Business Administration Major in Marketing Management', 'Bachelor of Arts in Communication'],
                'skills' => ['Digital Marketing' => 5, 'Market Research' => 3, 'Content Writing' => 3],
                'approved' => true,
                'image' => 'jobImages/softdev.png',
            ],
            [
                'title' => 'Civil Engineering Intern',
                'company' => 'BuildRight Construction',
                'address' => 'Malanday, Valenzuela City',
                'type' => 'Full-Time',
                'setup' => 'On-Site',
                'description' => 'Support site engineers with project estimation, drafting, and structural analysis on active building sites.',
                'hiring_limit' => 2,
                'industry' => 'Engineering & Construction',
                'programs' => ['Bachelor of Science in Civil Engineering'],
                'skills' => ['Project Estimation' => 5, 'AutoCAD' => 4, 'Structural Analysis' => 3],
                'approved' => true,
                'image' => 'jobImages/networkenginerr.png',
            ],
        ];

        foreach ($jobs as $data) {
            $employerUserId = Employer::where('employer_company_name', $data['company'])->value('user_id');
            $industryId = Industry::where('industry_name', $data['industry'])->value('industry_id');
            $programIds = Program::whereIn('program_name', $data['programs'])->pluck('program_id');

            $job = JobPosting::create([
                'job_posting_title' => $data['title'],
                'job_posting_company' => $data['company'],
                'job_posting_address' => $data['address'],
                'job_posting_employment_type' => $data['type'],
                'job_posting_setup' => $data['setup'],
                'job_posting_description' => $data['description'],
                'job_posting_date' => Carbon::now(),
                'job_closing_date' => Carbon::now()->addDays(30),
                'hiring_limit' => $data['hiring_limit'],
                'job_posting_image' => $data['image'],
                'job_approved' => $data['approved'],
                'industry_id' => $industryId,
                'user_id' => $employerUserId,
            ]);

            $job->programs()->attach($programIds);

            foreach ($data['skills'] as $skillName => $weight) {
                $skillId = Skill::where('skill_name', $skillName)->value('skill_id');
                if ($skillId) {
                    $job->skills()->attach($skillId, ['is_required' => true, 'weight' => $weight]);
                }
            }
        }
    }
}
