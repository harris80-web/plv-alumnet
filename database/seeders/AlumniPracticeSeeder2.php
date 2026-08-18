<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Industry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Section;
use App\Models\Skill;
use App\Models\User;
use App\Services\JobMatchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Second wave on top of AlumniPracticeSeeder — same shape (resume, AlumniId,
 * job applications against approved postings), but a separate class with a
 * non-colliding email pattern (alumni.wave2N@...) so it can run again later
 * without hitting the unique-email constraint AlumniPracticeSeeder would.
 * Run after JobPostingPracticeSeeder so these applicants are distributed
 * across the larger, now-realistic pool of open postings, not just the
 * original 10.
 */
class AlumniPracticeSeeder2 extends Seeder
{
    public const PASSWORD = 'Alumni@2026';

    private const FIRST_NAMES_MALE = [
        'Renato', 'Alvin', 'Bayani', 'Cesar', 'Domingo', 'Efren', 'Gregorio', 'Hernan',
        'Isagani', 'Jaime', 'Lorenzo', 'Mario', 'Nestor', 'Oscar', 'Pablo', 'Reynaldo',
    ];

    private const FIRST_NAMES_FEMALE = [
        'Amalia', 'Belen', 'Corazon', 'Dolores', 'Elena', 'Felisa', 'Gloria', 'Imelda',
        'Josefina', 'Leonora', 'Marites', 'Nenita', 'Ofelia', 'Perla', 'Remedios', 'Susana',
    ];

    private const LAST_NAMES = [
        'Abad', 'Buenaventura', 'Concepcion', 'Diaz', 'Esguerra', 'Feliciano', 'Gatchalian', 'Hernandez',
        'Ibarra', 'Jimenez', 'Lazaro', 'Mercado', 'Nolasco', 'Olivares', 'Pineda', 'Quintos',
        'Ramirez', 'Serrano', 'Tan', 'Uy', 'Vergara', 'Yatco',
    ];

    private const JOB_TITLES = [
        'Trainee', 'Junior Officer', 'Support Staff', 'Aide', 'Coordinator', 'Assistant Manager', 'Encoder', 'Liaison Officer',
    ];

    private const CERT_NAMES = [
        'Basic Occupational Safety', 'Time Management Essentials', 'Workplace Communication', 'Introduction to Data Privacy',
        'Conflict Resolution', 'Google Workspace Fundamentals', 'Basic Bookkeeping', 'Customer Relations',
    ];

    /** pending/under_review/ready_to_claim/claimed, weighted the same as the other alumni seeders. */
    private const ALUMNI_ID_STATUS_POOL = [
        'pending', 'pending', 'pending', 'pending',
        'under_review', 'under_review', 'under_review',
        'ready_to_claim', 'ready_to_claim',
        'claimed', 'claimed',
    ];

    public function run(): void
    {
        $programs = Program::all();
        $sections = Section::all();
        $industries = Industry::all();
        $skills = Skill::all();
        $jobPostings = JobPosting::approved()->get();
        $staffIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');

        if ($programs->isEmpty() || $sections->isEmpty() || $skills->isEmpty()) {
            $this->command->error('Programs, sections, or skills are empty — seed those first.');
            return;
        }

        $matchService = app(JobMatchService::class);
        $credentials = [];
        $alumniPool = collect();

        for ($i = 1; $i <= 30; $i++) {
            $email = "alumni.wave2{$i}@plvalumnet.test";
            if (User::where('user_email', $email)->exists()) {
                continue;
            }

            $isMale = fake()->boolean(48);
            $firstName = $isMale
                ? self::FIRST_NAMES_MALE[array_rand(self::FIRST_NAMES_MALE)]
                : self::FIRST_NAMES_FEMALE[array_rand(self::FIRST_NAMES_FEMALE)];
            $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            $gender = fake()->boolean(6) ? 'prefer_not_to_say' : ($isMale ? 'male' : 'female');

            $batch = fake()->numberBetween(2017, 2025);
            $program = $programs->random();

            $user = User::create([
                'user_email' => $email,
                'user_password' => Hash::make(self::PASSWORD),
                'user_first_name' => $firstName,
                'user_last_name' => $lastName,
                'user_middle_name' => self::LAST_NAMES[array_rand(self::LAST_NAMES)],
                'user_role' => 'alumni',
                'user_active' => true,
                'user_number' => '09' . fake()->numerify('#########'),
            ]);

            $isEmployed = fake()->boolean(45);
            $industry = $industries->isNotEmpty() ? $industries->random() : null;

            $alumnus = $user->alumnus()->create([
                'program_id' => $program->program_id,
                'section_id' => $sections->random()->section_id,
                'alumnus_gender' => $gender,
                'alumnus_batch' => $batch,
                'alumnus_employment_status' => $isEmployed,
                'industry_id' => $isEmployed ? $industry?->industry_id : null,
                'alumnus_workplace' => $isEmployed ? fake()->company() : null,
                'alumnus_workplace_undisclosed' => $isEmployed ? fake()->boolean(15) : false,
                'alumnus_first_job_date' => $isEmployed ? fake()->dateTimeBetween($batch . '-06-01', 'now')->format('Y-m-d') : null,
                'alumnus_resume_summary' => "Motivated {$program->program_name} graduate (batch {$batch}) with hands-on experience gained through coursework, internships, and campus projects. Eager to apply my training in a professional setting and grow within a supportive team.",
                'linkedin_url' => fake()->boolean(60) ? 'https://linkedin.com/in/' . Str::slug($firstName . '-' . $lastName . '-w2-' . $i) : null,
            ]);

            $alumnus->alumniId()->create([
                'status' => $status = self::ALUMNI_ID_STATUS_POOL[array_rand(self::ALUMNI_ID_STATUS_POOL)],
                'status_updated_at' => $status === 'pending' ? $alumnus->created_at : fake()->dateTimeBetween('-3 months', 'now'),
                'updated_by' => $status !== 'pending' && $staffIds->isNotEmpty() ? $staffIds->random() : null,
            ]);

            $alumnus->skills()->attach($skills->random(min($skills->count(), fake()->numberBetween(2, 5)))->pluck('skill_id'));

            for ($e = 0; $e < fake()->numberBetween(0, 2); $e++) {
                Experience::create([
                    'alumnus_id' => $alumnus->user_id,
                    'experience_type' => fake()->boolean(70) ? 'work' : 'project',
                    'industry_id' => $industries->isNotEmpty() ? $industries->random()->industry_id : $industry?->industry_id,
                    'experience_duration_months' => fake()->numberBetween(2, 30),
                    'experience_job_title' => self::JOB_TITLES[array_rand(self::JOB_TITLES)],
                    'experience_job_description' => fake()->sentence(12),
                ]);
            }

            for ($c = 0; $c < fake()->numberBetween(0, 2); $c++) {
                Certification::create([
                    'alumnus_id' => $alumnus->user_id,
                    'certification_type' => fake()->randomElement(['certification', 'seminar', 'training']),
                    'certification_name' => self::CERT_NAMES[array_rand(self::CERT_NAMES)],
                    'certification_from' => fake()->company(),
                    'certification_date' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                ]);
            }

            $alumnus->refreshResumeCompleteness();

            $credentials[] = ['name' => "{$firstName} {$lastName}", 'email' => $email];
            $alumniPool->push($alumnus->fresh(['skills', 'experiences', 'certifications']));
        }

        if ($alumniPool->isEmpty()) {
            return; // already seeded on a previous run — nothing new to connect to jobs
        }

        // ── Connect them to the job posting feature, same as AlumniPracticeSeeder.
        $applied = [];
        foreach ($jobPostings as $job) {
            $applicantCount = min($alumniPool->count(), fake()->numberBetween(2, 6));
            $applicants = $alumniPool->random(min($applicantCount, $alumniPool->count()));
            $applicants = $applicants instanceof Alumnus ? collect([$applicants]) : $applicants;

            foreach ($applicants as $alumnus) {
                $pairKey = $job->job_posting_id . '-' . $alumnus->user_id;
                if (isset($applied[$pairKey]) || ($alumnus->alumnus_resume_completeness ?? 0) <= 0) {
                    continue;
                }
                $applied[$pairKey] = true;

                $match = $matchService->scoreFor($job, $alumnus);

                $status = 'pending';
                if ($job->remainingHiringSlots() > 0 && fake()->boolean(12)) {
                    $status = 'hired';
                } elseif (fake()->boolean(18)) {
                    $status = 'shortlisted';
                } elseif (fake()->boolean(12)) {
                    $status = 'declined';
                }

                JobApplication::create([
                    'alumnus_id' => $alumnus->user_id,
                    'job_id' => $job->job_posting_id,
                    'application_status' => $status,
                    'application_score' => $match->score,
                    'is_read' => fake()->boolean(55),
                ]);

                if ($status === 'hired') {
                    $alumnus->alumnus_employment_status = true;
                    if ($job->industry_id) {
                        $alumnus->industry_id = $job->industry_id;
                    }
                    $alumnus->alumnus_workplace = $job->job_posting_company;
                    $alumnus->alumnus_workplace_undisclosed = false;
                    if (!$alumnus->alumnus_first_job_date) {
                        $alumnus->alumnus_first_job_date = now();
                    }
                    $alumnus->save();
                }
            }
        }

        $lines = ["Password for every wave-2 seeded account: " . self::PASSWORD, ''];
        foreach ($credentials as $cred) {
            $lines[] = "{$cred['email']}  |  " . self::PASSWORD . "  |  {$cred['name']}";
        }
        $outputPath = storage_path('app/alumni_practice_wave2_credentials.txt');
        file_put_contents($outputPath, implode(PHP_EOL, $lines));

        $this->command->info('Seeded ' . count($credentials) . ' additional alumni accounts + AlumniId records + job applications.');
        $this->command->info('Full credentials list written to: ' . $outputPath);
    }
}
