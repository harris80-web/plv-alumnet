<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Services\JobMatchService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobApplicationSeeder extends Seeder
{
    /**
     * Applies several seeded alumni to several seeded jobs, scoring each
     * pair through the real JobMatchService (so the compatibility ranking
     * on the employer's applicants page reflects genuine skill/program/
     * experience/certification overlap, not made-up numbers) and lands
     * them across a deliberate mix of statuses:
     *
     * - "IT Support Specialist" (hiring_limit 3) ends with 1 hired, 3 still
     *   pending — a "partial limit reached" scenario for bulk-hire.
     * - "Medical Records Clerk" (hiring_limit 1) ends fully hired — the
     *   "limit reached, nothing else hireable" scenario.
     * - "Full Stack Developer" / "HR Generalist" / "Elementary Teacher" are
     *   left with open slots and multiple pending applicants — the normal
     *   "check a few, hire" scenario.
     * - "Front-End Developer" stays unapproved with one pending applicant,
     *   for testing the admin job-approval queue.
     * - "Customer Success Associate" (open to every program, hiring_limit 4)
     *   gets all 14 first/second-wave alumni as pending applicants — a pool
     *   big enough that the applicants page's top-10 rank badge actually
     *   has unranked rows (11-14) to contrast against.
     *
     * Hire side effects are applied inline (mirrors
     * JobApplicationController::hireApplication()) instead of calling the
     * controller, so no mail gets sent while seeding.
     */
    public function run(): void
    {
        $applications = [
            ['alumnus' => 'alumni@example.com', 'job' => 'Full Stack Developer', 'status' => 'pending', 'read' => true],
            ['alumnus' => 'alumni@example.com', 'job' => 'IT Support Specialist', 'status' => 'hired', 'read' => true],
            ['alumnus' => 'alumni2@example.com', 'job' => 'Full Stack Developer', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni2@example.com', 'job' => 'Front-End Developer', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni2@example.com', 'job' => 'IT Support Specialist', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni3@example.com', 'job' => 'Full Stack Developer', 'status' => 'pending', 'read' => true],
            ['alumnus' => 'alumni3@example.com', 'job' => 'IT Support Specialist', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni4@example.com', 'job' => 'IT Support Specialist', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni4@example.com', 'job' => 'Civil Engineering Intern', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni5@example.com', 'job' => 'Medical Records Clerk', 'status' => 'hired', 'read' => true],
            ['alumnus' => 'alumni6@example.com', 'job' => 'Medical Records Clerk', 'status' => 'declined', 'read' => true],
            ['alumnus' => 'alumni7@example.com', 'job' => 'HR Generalist', 'status' => 'shortlisted', 'read' => true],
            ['alumnus' => 'alumni8@example.com', 'job' => 'HR Generalist', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni9@example.com', 'job' => 'Elementary Teacher', 'status' => 'pending', 'read' => true],
            ['alumnus' => 'alumni10@example.com', 'job' => 'Elementary Teacher', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni11@example.com', 'job' => 'Marketing Coordinator', 'status' => 'shortlisted', 'read' => true],
            ['alumnus' => 'alumni12@example.com', 'job' => 'Accounting Associate', 'status' => 'pending', 'read' => true],
            ['alumnus' => 'alumni13@example.com', 'job' => 'Accounting Associate', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni14@example.com', 'job' => 'Marketing Coordinator', 'status' => 'pending', 'read' => false],
            ['alumnus' => 'alumni15@example.com', 'job' => 'Civil Engineering Intern', 'status' => 'pending', 'read' => true],
        ];

        // Every alumnus from the first two waves (alumni@example.com plus
        // alumni2..alumni14) applies to the one program-agnostic job, so it
        // ends up with 14 applicants — enough to show ranks #1 through #10
        // plus a few unranked rows below the cut.
        foreach (range(1, 14) as $n) {
            $applications[] = [
                'alumnus' => $n === 1 ? 'alumni@example.com' : "alumni{$n}@example.com",
                'job' => 'Customer Success Associate',
                'status' => 'pending',
                'read' => $n % 3 === 0,
            ];
        }

        $matchService = app(JobMatchService::class);

        foreach ($applications as $data) {
            $alumnus = Alumnus::with(['skills', 'experiences', 'certifications'])
                ->whereHas('user', fn ($q) => $q->where('user_email', $data['alumnus']))
                ->first();

            $job = JobPosting::with(['skills', 'programs'])
                ->where('job_posting_title', $data['job'])
                ->first();

            if (!$alumnus || !$job) {
                continue;
            }

            $match = $matchService->scoreFor($job, $alumnus);

            $application = JobApplication::create([
                'alumnus_id' => $alumnus->user_id,
                'job_id' => $job->job_posting_id,
                'application_status' => $data['status'] === 'hired' ? 'pending' : $data['status'],
                'application_score' => $match->score,
                'is_read' => $data['read'],
            ]);

            if ($data['status'] === 'hired') {
                $this->hire($application, $job, $alumnus);
            }
        }
    }

    private function hire(JobApplication $application, JobPosting $job, Alumnus $alumnus): void
    {
        $application->application_status = 'hired';
        $application->save();

        $alumnus->alumnus_employment_status = true;
        if ($job->industry_id) {
            $alumnus->industry_id = $job->industry_id;
        }
        if ($job->job_posting_company) {
            $alumnus->alumnus_workplace = $job->job_posting_company;
            $alumnus->alumnus_workplace_undisclosed = false;
        }
        if (!$alumnus->alumnus_first_job_date) {
            $alumnus->alumnus_first_job_date = now();
        }
        $alumnus->save();
    }
}
