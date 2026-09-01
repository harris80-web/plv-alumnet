<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Skill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Replaces the old EmployerPracticeSeeder/JobPostingPracticeSeeder/
 * AlumniPracticeSeeder(+2) trio with one coherent 49-alumni + 49-employer
 * cohort (49, not 50 — Ryza Ison and Charles Tarog from AlumnusSeeder/
 * EmployerSeeder already exist, bringing each pool to a round 50 total).
 *
 * The core idea: employment status/industry/workplace on an alumnus is
 * never independently randomized — it's the actual downstream consequence
 * of a 'hired' job application, exactly like the real hire flow in
 * JobApplicationController::hireApplication(). That's what makes the
 * dashboard's Job-to-Degree Alignment chart show real, non-fabricated
 * variance instead of a number nobody could explain.
 *
 * Run standalone (not via the default `db:seed`, which would try to
 * recreate Ryza/Charles/Harris/Niña and the lookup tables):
 *   php artisan db:seed --class=CohortSeeder
 *   php artisan db:seed --class=AlumniYearbookSeeder
 *   php artisan db:seed --class=TestimonialPracticeSeeder
 *   php artisan db:seed --class=MessageSeeder
 * (the last three are already generic over "every current alumnus" and
 * "every current alumni pair" — no changes needed for them to pick up
 * this cohort.)
 */
class CohortSeeder extends Seeder
{
    private const ALUMNI_FIRST_NAMES = [
        'Marco', 'Isabelle', 'Rafael', 'Genevieve', 'Julian', 'Adrienne', 'Emmanuel', 'Katarina',
        'Sebastian', 'Lorraine', 'Dominic', 'Rosanna', 'Nathan', 'Precious', 'Vincent', 'Clarisse',
        'Gabriel', 'Andrea', 'Xavier', 'Franchesca', 'Renzo', 'Kimberly', 'Elijah', 'Michaela',
        'Ivan', 'Georgina', 'Tristan', 'Alyanna', 'Marcus', 'Bettina', 'Lorenzo', 'Vanessa',
        'Adrian', 'Charmaine', 'Benedict', 'Rosemarie', 'Christian', 'Danica', 'Wilfredo', 'Josephine',
        'Raymund', 'Loraine', 'Anselmo', 'Consolacion', 'Bartolome', 'Encarnacion', 'Domingo', 'Presentacion',
        'Eduardo', 'Milagros',
    ];

    private const ALUMNI_LAST_NAMES = [
        'Villaflor', 'Bautista', 'Reyes', 'Santiago', 'Cruz', 'Garcia', 'Mercado', 'Salonga',
        'Fajardo', 'Enriquez', 'Bonifacio', 'Ledesma', 'Panganiban', 'Quijano', 'Serrano', 'Tolentino',
        'Umali', 'Verdejo', 'Wenceslao', 'Yabut', 'Zamora', 'Abrigo', 'Belmonte', 'Cabahug',
        'Dionisio', 'Estacio', 'Formoso', 'Gatdula', 'Herrera', 'Ilagan', 'Jimenez', 'Katindig',
        'Lazaro', 'Montano', 'Nieva', 'Orlanes', 'Pascual', 'Quirante', 'Rosario', 'Sarmiento',
        'Trinidad', 'Uybarreta', 'Villareal', 'Wagas', 'Yldefonso', 'Zaide', 'Alcantara', 'Bermudez',
        'Custodio', 'Dagohoy',
    ];

    private const EMPLOYER_FIRST_NAMES = [
        'Antonio', 'Beatriz', 'Carmelo', 'Divina', 'Ernesto', 'Fe', 'Gilbert', 'Herminia',
        'Ignacio', 'Julieta', 'Leandro', 'Marilou', 'Norberto', 'Odessa', 'Pacifico', 'Remedios',
    ];

    private const EMPLOYER_LAST_NAMES = [
        'Alvarez', 'Bonifacio', 'Cabrera', 'Dizon', 'Espino', 'Fajardo', 'Guerrero', 'Hidalgo',
        'Isip', 'Javier', 'Katigbak', 'Lacson', 'Macaraeg', 'Nepomuceno', 'Ortiz', 'Peralta',
    ];

    private const COMPANY_WORDS = [
        'Redwood', 'Bayview', 'Skyline', 'Fieldstone', 'Lighthouse', 'Amberlee', 'Windmere', 'Oakcrest',
        'Sterling', 'Highpoint', 'Coastline', 'Ridgeline', 'Brightwell', 'Foxglove', 'Copperline', 'Larkspur',
        'Alderwood', 'Bellcrest', 'Cedarpoint', 'Driftwood',
    ];

    private const COMPANY_SUFFIXES = ['Inc.', 'Corp.', 'Group', 'Solutions', 'Enterprises', 'Co.', 'Holdings', 'Partners'];

    /**
     * Per industry: candidate job titles, the skills those roles look for,
     * and which programs the role would naturally suit. Used both to write
     * coherent job postings and — when deciding who gets hired where — to
     * bias hires toward a plausible industry, without hardcoding a second
     * copy of Program::ALIGNED_INDUSTRIES (that stays the single source of
     * truth the dashboard actually checks against).
     */
    private const TEMPLATES = [
        'Technology' => [
            'titles' => ['Backend Developer', 'QA Tester', 'Systems Analyst', 'UI/UX Designer', 'Mobile App Developer', 'Technical Support Engineer', 'Data Entry Specialist', 'IT Helpdesk Associate'],
            'skills' => ['PHP', 'Laravel', 'JavaScript', 'React', 'HTML & CSS', 'Git & GitHub', 'Network Administration'],
            'programs' => ['Bachelor of Science in Information Technology'],
        ],
        'Healthcare' => [
            'titles' => ['Medical Receptionist', 'Health Information Officer', 'Wellness Program Coordinator', 'Patient Care Assistant', 'Clinic Administrative Aide'],
            'skills' => ['Communication', 'Microsoft Excel', 'Psychological Assessment', 'Counseling'],
            'programs' => ['Bachelor of Science in Psychology', 'Bachelor of Science in Social Work'],
        ],
        'Business & Finance' => [
            'titles' => ['Junior Accountant', 'Financial Analyst', 'Sales Associate', 'Administrative Assistant', 'Payroll Officer', 'Brand Coordinator', 'Bookkeeper', 'Accounts Payable Clerk'],
            'skills' => ['Bookkeeping', 'Financial Auditing', 'Microsoft Excel', 'Financial Modeling', 'Digital Marketing', 'Content Writing', 'Recruitment & Selection'],
            'programs' => [
                'Bachelor of Science in Accountancy',
                'Bachelor of Science in Business Administration Major in Financial Management',
                'Bachelor of Science in Business Administration Major in Marketing Management',
                'Bachelor of Science in Business Administration Major in Human Resource Management',
            ],
        ],
        'Education' => [
            'titles' => ['Training Coordinator', 'Curriculum Assistant', 'Academic Tutor', 'Learning Support Specialist', 'Instructional Aide'],
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
            'titles' => ['Community Development Officer', 'Policy Research Assistant', 'Case Worker', 'Public Affairs Assistant', 'Records Officer'],
            'skills' => ['Policy Analysis', 'Public Administration', 'Community Organizing', 'Case Management'],
            'programs' => ['Bachelor of Public Administration', 'Bachelor of Science in Social Work'],
        ],
        'Engineering & Construction' => [
            'titles' => ['Junior Civil Engineer', 'Drafting Technician', 'Site Inspector', 'Electrical Maintenance Technician', 'Project Estimator'],
            'skills' => ['AutoCAD', 'Structural Analysis', 'Project Estimation', 'Circuit Design'],
            'programs' => ['Bachelor of Science in Civil Engineering', 'Bachelor of Science in Electrical Engineering'],
        ],
    ];

    private const IMAGES = [
        'jobImages/softdev.png',
        'jobImages/accounting.jpg',
        'jobImages/networkenginerr.png',
        'jobImages/q1y0JaVz4IA7oWkm3LLuU0fpBABRjt2Duyz7nYhI.png',
        'jobImages/QWLRxAmwXfRiRceBILODnbhQl3g5Og8JZswxwBzj.jpg',
        'jobImages/HbcdSxhcwBpO4tn4u4qnMYo8EMVVbvq64x59ieE8.jpg',
    ];

    /** Same weighting AlumnusSeeder uses, so both pools of alumni IDs look consistent. */
    private const ALUMNI_ID_STATUS_POOL = [
        'pending', 'pending', 'pending', 'pending',
        'under_review', 'under_review', 'under_review',
        'ready_to_claim', 'ready_to_claim',
        'claimed', 'claimed',
    ];

    public function run(): void
    {
        $employers = $this->seedEmployers(49);
        $alumni = $this->seedAlumni(49);
        $jobs = $this->seedJobPostings($employers);
        $this->seedApplications($alumni, $jobs);
    }

    private function seedEmployers(int $count): Collection
    {
        $industries = Industry::where('industry_name', '!=', 'None')->get();
        $created = collect();

        for ($i = 1; $i <= $count; $i++) {
            $email = "employer.cohort{$i}@plvalumnet.test";
            $company = self::COMPANY_WORDS[array_rand(self::COMPANY_WORDS)] . ' ' . self::COMPANY_SUFFIXES[array_rand(self::COMPANY_SUFFIXES)];
            $industry = $industries->random();
            // ~80% already verified, so the "awaiting approval" queue exists
            // without dominating the Company Registration Report.
            $approved = fake()->boolean(80);
            $joinedAt = Carbon::now()->subDays(fake()->numberBetween(1, 640));

            $user = User::create([
                'user_email' => $email,
                'user_password' => Hash::make('Employer@2026'),
                'user_first_name' => self::EMPLOYER_FIRST_NAMES[array_rand(self::EMPLOYER_FIRST_NAMES)],
                'user_last_name' => self::EMPLOYER_LAST_NAMES[array_rand(self::EMPLOYER_LAST_NAMES)],
                'user_role' => 'employer',
                'user_active' => $approved,
            ]);
            $user->forceFill(['created_at' => $joinedAt, 'updated_at' => $joinedAt])->save();

            $employer = $user->employer()->create([
                'employer_company_name' => $company,
                'employer_website_url' => 'https://www.' . Str::slug($company) . '.com',
                'employer_company_size' => fake()->numberBetween(5, 900),
                'employer_year_established' => fake()->numberBetween(1995, 2023),
                'industry_id' => $industry->industry_id,
                'employer_approved' => $approved,
                'employer_company_document' => 'companyDocuments/' . Str::slug($company) . '.pdf',
            ]);
            $employer->forceFill(['created_at' => $joinedAt, 'updated_at' => $joinedAt])->save();

            $created->push($employer);
        }

        return $created;
    }

    private function seedAlumni(int $count): Collection
    {
        // Cycled + shuffled rather than pure random, so every program and
        // every batch year gets real coverage instead of a few dominating
        // by chance — the whole point of this cohort is that every filter
        // on the dashboard has something to show.
        $programIds = Program::pluck('program_id');
        $programCycle = collect(range(0, $count - 1))->map(fn ($i) => $programIds[$i % $programIds->count()])->shuffle()->values();

        $batchYears = range(2016, 2026);
        $batchCycle = collect(range(0, $count - 1))->map(fn ($i) => $batchYears[$i % count($batchYears)])->shuffle()->values();

        $sectionIds = \App\Models\Section::pluck('section_id');
        $genderPool = array_merge(array_fill(0, 9, 'male'), array_fill(0, 9, 'female'), array_fill(0, 2, 'prefer_not_to_say'));
        $staffIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');

        $created = collect();

        for ($i = 0; $i < $count; $i++) {
            $first = self::ALUMNI_FIRST_NAMES[array_rand(self::ALUMNI_FIRST_NAMES)];
            $last = self::ALUMNI_LAST_NAMES[array_rand(self::ALUMNI_LAST_NAMES)];
            $email = 'alumni.cohort' . ($i + 1) . '@plvalumnet.test';
            $batch = $batchCycle[$i];

            // Most joined the platform sometime after graduating; a handful
            // very recently, so "New This Month" on User Management isn't
            // always empty. A few accounts are deactivated so the Account
            // Status filter has something besides "Active" to show.
            $joinedAt = $i < 4
                ? Carbon::now()->subDays(fake()->numberBetween(0, 20))
                : Carbon::now()->subDays(fake()->numberBetween(30, 720));
            $deactivated = $i >= 4 && fake()->boolean(10);

            $user = User::create([
                'user_email' => $email,
                'user_password' => Hash::make('alumni123'),
                'user_first_name' => $first,
                'user_last_name' => $last,
                'user_role' => 'alumni',
                'user_active' => !$deactivated,
            ]);
            $user->forceFill(['created_at' => $joinedAt, 'updated_at' => $joinedAt])->save();

            $alumnus = $user->alumnus()->create([
                'program_id' => $programCycle[$i],
                'section_id' => $sectionIds->random(),
                // alumnus_batch is a real date column — commencement dates
                // vary a bit (Mar–May) rather than all landing on the same
                // day, so batch-grouped reports have realistic spread.
                'alumnus_batch' => Carbon::create($batch, fake()->numberBetween(3, 5), fake()->numberBetween(1, 28)),
                'alumnus_gender' => $genderPool[array_rand($genderPool)],
                'alumnus_resume_summary' => fake()->sentence(rand(12, 20)),
                'linkedin_url' => fake()->boolean(60) ? 'https://linkedin.com/in/' . Str::slug($first . '-' . $last) : null,
            ]);
            $alumnus->forceFill(['created_at' => $joinedAt, 'updated_at' => $joinedAt])->save();

            // 2-4 random skills so the resume/job-matching side of the app
            // has something real to score against.
            $skillIds = Skill::inRandomOrder()->limit(fake()->numberBetween(2, 4))->pluck('skill_id');
            $alumnus->skills()->attach($skillIds);
            $alumnus->refreshResumeCompleteness();

            $idStatus = self::ALUMNI_ID_STATUS_POOL[array_rand(self::ALUMNI_ID_STATUS_POOL)];
            $idSubmittedAt = (clone $joinedAt)->addDays(fake()->numberBetween(0, 5));
            $alumniId = $alumnus->alumniId()->create([
                'status' => $idStatus,
                'status_updated_at' => $idStatus === 'pending' ? $idSubmittedAt : Carbon::now()->subDays(rand(1, 60)),
                'updated_by' => $idStatus !== 'pending' && $staffIds->isNotEmpty() ? $staffIds->random() : null,
            ]);
            $alumniId->forceFill(['created_at' => $idSubmittedAt, 'updated_at' => $idSubmittedAt])->save();

            // Employment status/industry/workplace are deliberately left
            // unset here — seedApplications() below sets them as the real
            // consequence of a hired application, not an independent guess.
            $created->push($alumnus);
        }

        return $created;
    }

    private function seedJobPostings(Collection $employers): Collection
    {
        $industryTemplates = self::TEMPLATES;
        $created = collect();
        $existingTitles = [];

        // Every employer posts 1-3 jobs — with 49 employers that's roughly
        // 70-100 postings, enough for every Job Management filter (type,
        // setup, program, date range) to have real spread without being
        // unwieldy to browse by hand.
        foreach ($employers as $employer) {
            $industryName = Industry::find($employer->industry_id)?->industry_name;
            $template = $industryTemplates[$industryName] ?? $industryTemplates[array_rand($industryTemplates)];

            $postCount = fake()->numberBetween(1, 3);
            for ($n = 0; $n < $postCount; $n++) {
                $title = $template['titles'][array_rand($template['titles'])];
                $fullTitle = $title;
                $suffix = 1;
                while (in_array($employer->employer_company_name . '|' . $fullTitle, $existingTitles, true)) {
                    $fullTitle = $title . ' ' . ++$suffix;
                }
                $existingTitles[] = $employer->employer_company_name . '|' . $fullTitle;

                $programIds = Program::whereIn('program_name', $template['programs'])->pluck('program_id')->shuffle()->take(3);
                $skillNames = collect($template['skills'])->shuffle()->take(fake()->numberBetween(2, 4));

                // Spread across "today" / "this week" / "this month" /
                // "last 6 months" / "this year" / older-than-a-year, so
                // every Date Posted checkbox on Job Management actually
                // filters something in or out.
                $postedRoll = fake()->numberBetween(1, 100);
                $postedAt = match (true) {
                    $postedRoll <= 8 => Carbon::now()->subHours(fake()->numberBetween(0, 20)), // today
                    $postedRoll <= 20 => Carbon::now()->subDays(fake()->numberBetween(1, 6)), // this week
                    $postedRoll <= 40 => Carbon::now()->subDays(fake()->numberBetween(7, 27)), // this month
                    $postedRoll <= 75 => Carbon::now()->subDays(fake()->numberBetween(28, 179)), // last 6 months
                    $postedRoll <= 92 => Carbon::now()->subDays(fake()->numberBetween(180, 300)), // this year, older
                    default => Carbon::now()->subDays(fake()->numberBetween(370, 640)), // over a year old
                };

                $closingRoll = fake()->numberBetween(1, 100);
                $closingDate = match (true) {
                    $closingRoll <= 20 => Carbon::now()->subDays(fake()->numberBetween(1, 30)), // already closed
                    $closingRoll <= 35 => Carbon::now()->addDays(fake()->numberBetween(1, 7)), // closing this week
                    default => Carbon::now()->addDays(fake()->numberBetween(15, 120)),
                };

                $job = JobPosting::create([
                    'job_posting_title' => $fullTitle,
                    'job_posting_company' => $employer->employer_company_name,
                    'job_posting_address' => fake()->randomElement(['Valenzuela City', 'Quezon City', 'Caloocan City', 'Manila', 'Malabon City', 'Navotas City']) . ', Metro Manila',
                    'job_posting_employment_type' => fake()->randomElement(['Full-Time', 'Full-Time', 'Part-Time', 'Freelance']),
                    'job_posting_setup' => fake()->randomElement(['On-Site', 'Remote', 'Hybrid']),
                    'job_posting_description' => "We are looking for a {$fullTitle} to join {$employer->employer_company_name}. " . fake()->paragraph(3),
                    'job_closing_date' => $closingDate,
                    'hiring_limit' => fake()->numberBetween(1, 5),
                    'job_posting_image' => self::IMAGES[array_rand(self::IMAGES)],
                    'job_approved' => fake()->boolean(78),
                    'industry_id' => $employer->industry_id,
                    'user_id' => $employer->user_id,
                ]);
                // job_posting_date/created_at aren't mass-assignable — set
                // directly so both the displayed posting date and the
                // Date Posted filter (which reads created_at) agree.
                $job->forceFill(['job_posting_date' => $postedAt->toDateString(), 'created_at' => $postedAt, 'updated_at' => $postedAt])->save();

                $job->programs()->attach($programIds);
                foreach ($skillNames as $skillName) {
                    $skillId = Skill::where('skill_name', $skillName)->value('skill_id');
                    if ($skillId) {
                        $job->skills()->attach($skillId, ['is_required' => true]);
                    }
                }

                $created->push($job);
            }
        }

        return $created;
    }

    private function seedApplications(Collection $alumni, Collection $jobsAll): void
    {
        $jobs = $jobsAll->where('job_approved', true)->values();
        if ($jobs->isEmpty()) {
            return;
        }

        // Plain array, not a Collection — Collection::offsetGet() returns by
        // value, so `$collection[$id]--` silently no-ops (PHP warns
        // "indirect modification... has no effect") and hiring_limit would
        // never actually be enforced below.
        $remainingSlots = $jobs->pluck('hiring_limit', 'job_posting_id')->all();
        $industryById = Industry::pluck('industry_name', 'industry_id');

        // ~58% of alumni end up hired — close enough to a realistic
        // "majority employed, meaningful minority not" split without being
        // a round, suspicious number.
        $alumniShuffled = $alumni->shuffle();
        $hireCount = (int) round($alumni->count() * 0.58);

        foreach ($alumniShuffled as $index => $alumnus) {
            $shouldHire = $index < $hireCount;

            if ($shouldHire) {
                // 65% of the time, bias toward a job whose template
                // actually lists this alumnus's program (a plausible,
                // often-aligned hire); 35% of the time, pick from any job
                // with an open slot regardless of fit — real cross-industry
                // hires happen, and programs with no template entry at all
                // (e.g. Communication) only ever land here, which mirrors
                // Program::ALIGNED_INDUSTRIES leaving them out on purpose.
                $programName = $alumnus->program->program_name ?? null;
                $candidateJobs = $jobs->filter(fn ($j) => ($remainingSlots[$j->job_posting_id] ?? 0) > 0);

                $preferred = $candidateJobs->filter(function ($j) use ($programName, $industryById) {
                    $industryName = $industryById[$j->industry_id] ?? null;
                    $template = self::TEMPLATES[$industryName] ?? null;
                    return $template && in_array($programName, $template['programs'], true);
                });

                $pool = (fake()->boolean(65) && $preferred->isNotEmpty()) ? $preferred : $candidateJobs;
                if ($pool->isEmpty()) {
                    $pool = $candidateJobs;
                }
                if ($pool->isEmpty()) {
                    continue; // every job is fully staffed — skip rather than force an over-hire
                }

                $job = $pool->random();
                $remainingSlots[$job->job_posting_id]--;

                // Hires skew recent (so the default 6-month "Hires per
                // Month" view isn't sparse) but reach back a full 24
                // months, so the 12/24-month range options show real history too.
                $recencyRoll = fake()->numberBetween(1, 100);
                $monthsAgo = match (true) {
                    $recencyRoll <= 50 => fake()->numberBetween(0, 5),
                    $recencyRoll <= 80 => fake()->numberBetween(6, 11),
                    default => fake()->numberBetween(12, 23),
                };
                $hiredAt = Carbon::now()->subMonths($monthsAgo)->subDays(fake()->numberBetween(0, 27));
                $appliedAt = (clone $hiredAt)->subDays(fake()->numberBetween(3, 21));

                $application = JobApplication::create([
                    'alumnus_id' => $alumnus->user_id,
                    'job_id' => $job->job_posting_id,
                    'application_date' => $appliedAt,
                    'application_status' => 'hired',
                    'hired_at' => $hiredAt,
                    'application_score' => fake()->randomFloat(2, 55, 98),
                    'is_read' => true,
                ]);
                $application->forceFill(['created_at' => $appliedAt, 'updated_at' => $hiredAt])->save();

                // Mirror JobApplicationController::hireApplication()'s side
                // effects exactly, so this alumnus's profile is consistent
                // with the application that actually produced it.
                $alumnus->alumnus_employment_status = true;
                $alumnus->industry_id = $job->industry_id;
                $alumnus->alumnus_workplace = $job->job_posting_company;
                $alumnus->alumnus_workplace_undisclosed = fake()->boolean(12);
                $alumnus->alumnus_job_position = $job->job_posting_title;
                $alumnus->alumnus_employment_date = $hiredAt;
                $alumnus->alumnus_employed_via_platform = true;
                $alumnus->alumnus_first_job_date = $hiredAt;
                $alumnus->save();
            }

            // Every alumnus also has 1-4 non-hired applications scattered
            // across other postings, so the applicant lists / Total
            // Applications count reflect real application volume, not just
            // the winning one.
            $extraCount = fake()->numberBetween(1, 4);
            for ($n = 0; $n < $extraCount; $n++) {
                $job = $jobs->random();
                $status = fake()->randomElement(['pending', 'pending', 'shortlisted', 'declined']);
                $appliedAt = Carbon::now()->subDays(fake()->numberBetween(1, 300));

                $application = JobApplication::create([
                    'alumnus_id' => $alumnus->user_id,
                    'job_id' => $job->job_posting_id,
                    'application_status' => $status,
                    'application_score' => fake()->randomFloat(2, 30, 90),
                    'is_read' => fake()->boolean(60),
                ]);
                $application->forceFill(['created_at' => $appliedAt, 'updated_at' => $appliedAt])->save();
            }
        }
    }
}
