<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\JobPosting;
use App\Models\Office;
use App\Services\JobMatchService;
use App\Support\SimplePdf;
use Carbon\Carbon;
use Database\Seeders\Data\SeedCatalog as C;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds a full, realistic-looking dataset: 2 super admins, 15 admins,
 * 1000 alumni and 100 employers, with job posts, applications, hires,
 * reviews, ID/yearbook records, notices, testimonials, messages, flags and
 * chatbot tickets — varied enough that every report and filter has data.
 *
 * Login emails follow the existing pattern (alumni@example.com,
 * alumni2@example.com, …; employer@, admin@, superadmin@ likewise) and the
 * existing per-role passwords: alumni123, employer123, password123 (staff).
 *
 * Images come from seed-images/: generated portraits and logos
 * (php artisan seed:generate-art), job covers (seed-images/jobs) and notice
 * images (seed-images/notices). Deterministic: the same seed always produces
 * the same data.
 */
class RealisticSeeder extends Seeder
{
    private const ALUMNI = 1000;
    private const EMPLOYERS = 100;
    private const ADMINS = 15;

    private const GENERIC_POSITIONS = ['Customer Service Representative', 'Administrative Assistant', 'Sales Associate', 'Virtual Assistant', 'Data Encoder', 'Operations Associate', 'Bank Teller', 'Call Center Agent'];

    private const DECLINE_REASONS = [
        'Incomplete job description — please include the salary range and complete requirements.',
        'Company address is missing or could not be verified.',
        'This appears to be a duplicate of an existing job post.',
        'Job requirements are not related to any PLV program. Please revise the qualifications.',
    ];

    /** Admin roles: a realistic mix of feature permissions (admin@example.com gets everything). */
    private const ADMIN_ROLES = [
        ['alumni_id_management', 'reports'],
        ['job_management', 'reports', 'messaging'],
        ['notices', 'testimonials', 'faqs'],
        ['messaging', 'faqs'],
        ['user_management', 'alumni_id_management'],
        ['user_management', 'job_management', 'notices', 'messaging', 'reports'],
        ['testimonials', 'notices', 'reports'],
    ];

    private Carbon $now;
    private Carbon $launch;
    private $disk;
    private array $hash = [];
    private array $programs = [];       // program_id => ['name', 'college', 'p' => catalog profile]
    private array $industryId = [];     // name => id
    private array $skillId = [];        // name => id
    private array $pools = ['female' => [], 'male' => [], 'logo' => []];
    private array $poolNext = ['female' => 0, 'male' => 0, 'logo' => 0];
    private array $staff = [];          // user_id => [role, perms, office_id, name]
    private array $alumni = [];         // user_id => info
    private array $employers = [];      // user_id => info
    private array $jobs = [];           // job_posting_id => info
    private array $applications = [];   // [job_id, alumnus_id, status]
    private array $notifications = [];
    private array $usedCompanies = [];

    public function run(): void
    {
        mt_srand(20261107);
        $this->now = now();
        $this->launch = Carbon::create(2025, 1, 13, 8);
        $this->disk = Storage::disk('public');
        $this->hash = [
            'alumni' => Hash::make('alumni123'),
            'employer' => Hash::make('employer123'),
            'staff' => Hash::make('password123'),
        ];

        $this->loadReference();
        $this->loadImagePools();

        $steps = [
            'staff accounts' => 'seedStaff',
            'alumni accounts and resumes' => 'seedAlumni',
            'employers' => 'seedEmployers',
            'job posts' => 'seedJobs',
            'applications and hires' => 'seedApplications',
            'bookmarks, votes and reviews' => 'seedEngagement',
            'alumni IDs and yearbooks' => 'seedIdsAndYearbooks',
            'events, seminars and announcements' => 'seedNotices',
            'testimonials' => 'seedTestimonials',
            'messages and flags' => 'seedMessages',
            'chatbot tickets' => 'seedChatbot',
            'notifications' => 'flushNotifications',
            'resume completeness and match scores' => 'finalize',
        ];
        foreach ($steps as $label => $method) {
            $this->command?->info("  · {$label}");
            DB::transaction(fn () => $this->{$method}());
        }
    }

    // ───────────────────────── reference data, images, helpers ─────────────────────────

    private function loadReference(): void
    {
        foreach (DB::table('programs')->get() as $p) {
            $this->programs[$p->program_id] = ['name' => $p->program_name, 'college' => $p->college, 'p' => C::PROGRAMS[$p->program_name] ?? null];
        }
        $this->industryId = DB::table('industries')->pluck('industry_id', 'industry_name')->all();
        $this->skillId = DB::table('skills')->pluck('skill_id', 'skill_name')->all();
    }

    private function loadImagePools(): void
    {
        $dir = base_path('seed-images/generated');
        if (!is_dir("$dir/profiles") || count(glob("$dir/profiles/female-*.png")) < 50) {
            $this->command?->warn('  Generating portrait/logo pools first (seed:generate-art)...');
            Artisan::call('seed:generate-art');
        }
        $this->pools['female'] = glob("$dir/profiles/female-*.png");
        $this->pools['male'] = glob("$dir/profiles/male-*.png");
        $this->pools['logo'] = glob("$dir/logos/logo-*.png");
        foreach ($this->pools as &$pool) {
            sort($pool, SORT_NATURAL);
            shuffle($pool);
        }
    }

    private function chance(int $pct): bool { return mt_rand(1, 100) <= $pct; }
    private function pick(array $a) { return $a[mt_rand(0, count($a) - 1)]; }
    private function sample(array $a, int $n): array
    {
        shuffle($a);
        return array_slice($a, 0, max(0, min($n, count($a))));
    }
    private function weighted(array $weights)
    {
        $r = mt_rand(1, array_sum($weights));
        foreach ($weights as $key => $w) {
            if (($r -= $w) <= 0) return $key;
        }
        return array_key_first($weights);
    }
    private function between(Carbon $a, Carbon $b): Carbon
    {
        return $b->lte($a) ? $a->copy() : Carbon::createFromTimestamp(mt_rand($a->timestamp, $b->timestamp));
    }
    private function ts(?Carbon $c): ?string { return $c?->format('Y-m-d H:i:s'); }
    private function maxC(Carbon $a, Carbon $b): Carbon { return ($a->gt($b) ? $a : $b)->copy(); }
    private function minC(Carbon $a, Carbon $b): Carbon { return ($a->lt($b) ? $a : $b)->copy(); }
    private function phone(): string { return '09' . mt_rand(15, 99) . str_pad((string) mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT); }

    private function address(bool $local = true): string
    {
        return $local
            ? mt_rand(1, 350) . ' ' . $this->pick(C::STREETS) . ', ' . $this->pick(C::BARANGAYS) . ', Valenzuela City'
            : mt_rand(1, 999) . ' ' . $this->pick(C::OTHER_CITY_STREETS) . ', ' . $this->pick(C::OTHER_CITIES);
    }

    private function personName(string $gender): array
    {
        $firsts = match ($gender) {
            'male' => C::FIRST_MALE,
            'female' => C::FIRST_FEMALE,
            default => array_merge(C::FIRST_MALE, C::FIRST_FEMALE),
        };
        $last = $this->pick(C::LAST);
        do { $middle = $this->pick(C::LAST); } while ($middle === $last);

        return [$this->pick($firsts), $last, $middle];
    }

    private function storeImage(string $source, string $dir, string $name): string
    {
        $path = "$dir/$name";
        if (!$this->disk->exists($path)) {
            $this->disk->put($path, file_get_contents($source));
        }
        return $path;
    }

    private function portrait(string $gender, string $tag): string
    {
        $g = in_array($gender, ['male', 'female'], true) ? $gender : $this->pick(['male', 'female']);
        $pool = $this->pools[$g];
        $file = $pool[$this->poolNext[$g]++ % count($pool)];

        return $this->storeImage($file, 'profilePictures', "seed-{$tag}.png");
    }

    private function shortProgram(string $name): string
    {
        return trim(str_replace(['Bachelor of Science in Business Administration Major in ', 'Bachelor of Science in ', 'Bachelor of Arts in ', 'Bachelor of Secondary Education Major in ', 'Bachelor of '], ['', '', '', 'Secondary Education – ', ''], $name));
    }

    private function companyName(string $industry, bool $unique = false): string
    {
        $suffixes = C::COMPANY_SUFFIX[$industry] ?? C::COMPANY_SUFFIX['Business & Finance'];
        for ($try = 0; $try < 200; $try++) {
            $name = $this->pick(C::COMPANY_PREFIX) . ' ' . $this->pick($suffixes);
            if (!$unique || !isset($this->usedCompanies[$name])) {
                $this->usedCompanies[$name] = true;
                return $name;
            }
        }
        return $name . ' ' . mt_rand(2, 9);
    }

    private function workplaceName(string $industry): string
    {
        if ($industry === 'Government & Public Service' && $this->chance(55)) {
            return $this->pick(['City Government of Valenzuela', 'Barangay ' . $this->pick(C::BARANGAYS) . ' Hall', 'City Social Welfare and Development Office', 'Public Employment Service Office – Valenzuela']);
        }
        if ($industry === 'Education' && $this->chance(55)) {
            return $this->pick(['Schools Division Office of Valenzuela City', $this->pick(C::BARANGAYS) . ' Elementary School', $this->pick(C::BARANGAYS) . ' National High School', 'Pamantasan ng Lungsod ng Valenzuela']);
        }
        return $this->companyName($industry);
    }

    private function notify(int $userId, string $type, string $title, string $body, ?int $ref, Carbon $at, int $readPct = 60): void
    {
        $this->notifications[] = [
            'user_id' => $userId, 'type' => $type, 'title' => $title, 'body' => $body, 'reference_id' => $ref,
            'read_at' => $this->chance($readPct) ? $this->ts($this->between($at, $this->now)) : null,
            'created_at' => $this->ts($at), 'updated_at' => $this->ts($at),
        ];
    }

    private function insertChunked(string $table, array $rows, int $size = 500): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    private function staffWith(string $perm): array
    {
        return array_keys(array_filter($this->staff, fn ($s) => $s['role'] === 'super_admin' || in_array($perm, $s['perms'], true)));
    }

    // ───────────────────────── staff ─────────────────────────

    private function seedStaff(): void
    {
        $allPerms = array_keys(Office::PERMISSIONS);
        $people = [
            ['super_admin', 'superadmin@example.com', 'Harris', 'Cruz', 'male', null],
            ['super_admin', 'superadmin2@example.com', null, null, 'female', null],
            ['admin', 'admin@example.com', 'Niña', 'Dela Cruz', 'female', $allPerms],
        ];
        for ($i = 2; $i <= self::ADMINS; $i++) {
            $perms = self::ADMIN_ROLES[($i - 2) % count(self::ADMIN_ROLES)];
            if ($this->chance(35)) $perms[] = $this->pick($allPerms);
            $people[] = ['admin', "admin{$i}@example.com", null, null, $this->pick(['male', 'female']), array_values(array_unique($perms))];
        }

        foreach ($people as $k => [$role, $email, $first, $last, $gender, $perms]) {
            if (!$first) [$first, $last] = $this->personName($gender);
            $created = $this->between(Carbon::create(2024, 11, 4), $this->launch);
            $uid = DB::table('users')->insertGetId([
                'user_password' => $this->hash['staff'], 'user_role' => $role,
                'user_first_name' => $first, 'user_last_name' => $last, 'user_middle_name' => $this->pick(C::LAST), 'user_suffix' => null,
                'user_email' => $email, 'user_number' => $this->phone(),
                'user_profile_picture' => $this->portrait($gender, 'staff-' . ($k + 1)),
                'user_active' => 1, 'last_active_at' => $this->ts($this->between($this->now->copy()->subDays(6), $this->now)),
                'user_muted' => 0, 'must_change_password' => 0,
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
            ]);
            $officeId = DB::table('offices')->insertGetId([
                'user_id' => $uid,
                'office_address' => $this->address($this->chance(70)),
                'permissions' => $role === 'admin' ? json_encode($perms) : null,
                'office_birth_date' => Carbon::create(mt_rand(1972, 1997), mt_rand(1, 12), mt_rand(1, 28))->toDateString(),
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
            ]);
            $this->staff[$uid] = ['role' => $role, 'perms' => $perms ?? $allPerms, 'office_id' => $officeId, 'name' => "$first $last"];
        }
    }

    // ───────────────────────── alumni ─────────────────────────

    private function seedAlumni(): void
    {
        // Every course gets at least 22 alumni; the rest follow realistic enrolment weights.
        $weightsByName = [
            'Bachelor of Science in Information Technology' => 18, 'Bachelor of Science in Accountancy' => 9,
            'Bachelor of Science in Business Administration Major in Human Resource Management' => 8,
            'Bachelor of Science in Business Administration Major in Marketing Management' => 8,
            'Bachelor of Science in Business Administration Major in Financial Management' => 6,
            'Bachelor of Science in Civil Engineering' => 6, 'Bachelor of Science in Electrical Engineering' => 5,
            'Bachelor of Science in Psychology' => 7, 'Bachelor of Arts in Communication' => 6,
            'Bachelor of Early Childhood Education' => 5, 'Bachelor of Public Administration' => 6,
            'Bachelor of Science in Social Work' => 5,
        ];
        $weights = [];
        foreach ($this->programs as $pid => $p) $weights[$pid] = $weightsByName[$p['name']] ?? 3;
        $assign = [];
        foreach (array_keys($this->programs) as $pid) for ($k = 0; $k < 22; $k++) $assign[] = $pid;
        while (count($assign) < self::ALUMNI) $assign[] = $this->weighted($weights);
        shuffle($assign);
        $itId = collect($this->programs)->search(fn ($p) => $p['name'] === 'Bachelor of Science in Information Technology');

        $year = (int) $this->now->year;
        $batchWeights = [];
        for ($y = $year - 12; $y <= $year; $y++) $batchWeights[$y] = 4 + ($y - ($year - 12)) * 2;

        $skillRows = $expRows = $certRows = [];
        $recentEmployed = [];

        for ($i = 1; $i <= self::ALUMNI; $i++) {
            $isDemo = $i === 1;
            $pid = $isDemo ? $itId : $assign[$i - 1];
            $prog = $this->programs[$pid];
            $profile = $prog['p'];
            $gender = $isDemo ? 'female' : $this->weighted(['female' => 52, 'male' => 45, 'prefer_not_to_say' => 3]);
            [$first, $last, $middle] = $isDemo ? ['Ryza', 'Ison', 'Santos'] : $this->personName($gender);
            $batchYear = $isDemo ? 2023 : (int) $this->weighted($batchWeights);
            $batch = Carbon::create($batchYear, mt_rand(6, 7), mt_rand(1, 28));
            $created = $this->between($this->maxC($this->launch, $batch->copy()->addDays(10)), $this->now->copy()->subDays(2));
            $status = $isDemo ? 'active' : $this->weighted(['active' => 88, 'deactivated' => 8, 'new' => 4]);
            $lastActive = $status === 'new' ? null : $this->between($created, $status === 'active' ? $this->now : $this->minC($created->copy()->addDays(120), $this->now));

            // Employment
            $pEmployed = $batchYear >= $year ? 35 : ($batchYear === $year - 1 ? 62 : 82);
            // Every course gets at least two employed graduates in the current year's batch,
            // so the dashboard's default (current-year) filters never show an empty course.
            $needsRecentHire = $batchYear === $year && ($recentEmployed[$pid] ?? 0) < 2;
            $employed = $isDemo || $needsRecentHire || $this->chance($pEmployed);
            if ($employed && $batchYear === $year) $recentEmployed[$pid] = ($recentEmployed[$pid] ?? 0) + 1;
            $emp = ['status' => 0, 'industry' => null, 'workplace' => null, 'undisclosed' => 0, 'position' => null, 'first' => null, 'intern' => null, 'date' => null];
            if ($employed) {
                $aligned = $isDemo || $this->chance(75);
                $industry = $aligned ? $profile['industry'] : $this->pick(array_values(array_diff(array_keys($this->industryId), ['None', $profile['industry']])));
                $before = !$isDemo && $this->chance(13);
                $firstJob = $before
                    ? $batch->copy()->subMonths(mt_rand(1, 8))
                    : $batch->copy()->addDays((int) (pow(mt_rand(0, 1000) / 1000, 2) * 540) + 7);
                if ($firstJob->gt($this->now)) $firstJob = $this->between($batch, $this->now->copy()->subDays(3));
                $date = $this->chance(70) ? $firstJob->copy() : $this->between($firstJob, $this->now->copy()->subDays(3));
                $undisclosed = !$isDemo && $this->chance(8);
                $emp = [
                    'status' => 1, 'industry' => $this->industryId[$industry] ?? null,
                    'workplace' => $undisclosed ? null : ($isDemo ? 'Bluewave Digital Solutions' : $this->workplaceName($industry)),
                    'undisclosed' => $undisclosed ? 1 : 0,
                    'position' => $isDemo ? 'Junior Web Developer' : ($aligned ? $this->pick($profile['positions']) : $this->pick(self::GENERIC_POSITIONS)),
                    'first' => $firstJob, 'intern' => $before ? ($this->chance(55) ? 1 : 0) : ($this->chance(8) ? 1 : 0), 'date' => $date,
                ];
            }

            // Resume
            $hasBuilder = $isDemo || $this->chance(82);
            $partial = !$isDemo && $hasBuilder && $this->chance(18);
            $hasFile = $isDemo || $this->chance(22);
            $hasCover = $isDemo || $this->chance(8);
            $linkedin = $hasBuilder && ($isDemo || (!$partial && $this->chance(45)))
                ? 'https://www.linkedin.com/in/' . Str::slug("$first $last") . '-' . mt_rand(100, 999) : null;
            $summary = null;
            if ($hasBuilder && !($partial && $this->chance(50))) {
                $summary = $profile['summary'] . ($employed && $emp['position'] && $emp['workplace'] ? " Currently working as {$emp['position']} at {$emp['workplace']}." : ' Open to full-time opportunities.');
            }

            $uid = DB::table('users')->insertGetId([
                'user_password' => $this->hash['alumni'], 'user_role' => 'alumni',
                'user_first_name' => $first, 'user_last_name' => $last,
                'user_middle_name' => $isDemo || $this->chance(82) ? $middle : null,
                'user_suffix' => $gender === 'male' && $this->chance(4) ? $this->pick(C::SUFFIXES) : null,
                'user_email' => $isDemo ? 'alumni@example.com' : "alumni{$i}@example.com",
                'user_number' => $isDemo || $this->chance(85) ? $this->phone() : null,
                'user_profile_picture' => $isDemo || $this->chance(86) ? $this->portrait($gender, "alumni-$i") : null,
                'user_active' => $status === 'deactivated' ? 0 : 1, 'last_active_at' => $this->ts($lastActive),
                'user_muted' => 0, 'must_change_password' => $status === 'new' ? 1 : 0,
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($lastActive ?? $created),
            ]);

            $resumeFile = $hasFile ? $this->resumePdf($uid, "$first $last", $prog['name'], $summary ?? $profile['summary'], $profile) : null;
            $coverFile = $hasCover ? $this->coverLetterPdf($uid, "$first $last", $prog['name']) : null;

            DB::table('alumni')->insert([
                'user_id' => $uid, 'program_id' => $pid, 'alumnus_gender' => $gender, 'industry_id' => $emp['industry'],
                'alumnus_workplace' => $emp['workplace'], 'alumnus_job_position' => $emp['position'],
                'alumnus_workplace_undisclosed' => $emp['undisclosed'],
                'alumnus_first_job_date' => $emp['first']?->toDateString(), 'alumnus_first_job_is_internship' => $emp['intern'],
                'alumnus_employment_date' => $emp['date']?->toDateString(), 'alumnus_employed_via_platform' => 0,
                'alumnus_employment_status' => $emp['status'], 'alumnus_resume_summary' => $summary,
                'alumnus_resume_file_path' => $resumeFile, 'alumnus_resume_backup_file_path' => null,
                'alumnus_cover_letter_file_path' => $coverFile, 'linkedin_url' => $linkedin, 'alumnus_resume_completeness' => 0,
                'alumnus_show_skills' => $this->chance(85) ? 1 : 0, 'alumnus_show_email' => $this->chance(50) ? 1 : 0,
                'alumnus_show_linkedin' => $linkedin && $this->chance(70) ? 1 : 0,
                'alumnus_batch' => $batch->toDateString(), 'alumnus_is_public' => $isDemo || $this->chance(92) ? 1 : 0,
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($lastActive ?? $created),
            ]);

            // Skills, experiences and certifications (built-in resume).
            $skills = $experiences = $certs = [];
            if ($hasBuilder) {
                $skills = $this->sample($profile['skills'], mt_rand(4, 7));
                if ($this->chance(60)) $skills[] = $this->pick(['Teamwork', 'Time Management', 'Adaptability', 'Leadership', 'Problem Solving']);
                $skills = array_values(array_unique(array_filter($skills, fn ($s) => isset($this->skillId[$s]))));
                if ($partial && $this->chance(30)) $skills = [];
                foreach ($skills as $s) {
                    $skillRows[] = ['alumnus_id' => $uid, 'skill_id' => $this->skillId[$s], 'created_at' => $this->ts($created), 'updated_at' => $this->ts($created)];
                }

                if (!$partial || $this->chance(50)) {
                    if ($employed && $emp['position']) {
                        $months = max(1, (int) $emp['date']->diffInMonths($this->now));
                        $experiences[] = ['work', $emp['position'], 'Handles day-to-day responsibilities as ' . $emp['position'] . ($emp['workplace'] ? ' at ' . $emp['workplace'] : '') . ', working closely with the team to meet targets and deadlines.', $emp['date'], null, $months, $emp['industry']];
                    }
                    foreach ($this->sample($profile['projects'], mt_rand(1, 2)) as $proj) {
                        $isWork = (bool) preg_match('/^(OJT|Practice teaching|Practicum|Field instruction)/i', $proj);
                        $months = $isWork ? mt_rand(2, 6) : mt_rand(2, 8);
                        $end = $batch->copy()->subMonths(mt_rand(0, 6));
                        $experiences[] = [$isWork ? 'work' : 'project', $proj, $isWork ? 'Assisted the team with daily tasks, documentation and reports while applying classroom learning in a real workplace.' : 'Planned and delivered the project with a small team, from research and design to presentation of results.', $end->copy()->subMonths($months), $end, $months, $isWork ? ($this->industryId[$profile['industry']] ?? null) : null];
                    }
                }
                foreach ($experiences as [$type, $title, $desc, $start, $end, $months, $ind]) {
                    $expRows[] = [
                        'alumnus_id' => $uid, 'experience_type' => $type, 'experience_start_date' => $start?->toDateString(),
                        'experience_end_date' => $end?->toDateString(), 'industry_id' => $ind, 'experience_duration_months' => $months,
                        'experience_job_title' => $title, 'experience_job_description' => $desc,
                        'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
                    ];
                }

                if (!$partial && ($isDemo || $this->chance(72))) {
                    foreach ($this->sample($profile['certs'], mt_rand(1, 3)) as [$cname, $ctype, $from]) {
                        $cdate = $this->between($batch->copy()->subMonths(8), $this->minC($this->now, $batch->copy()->addMonths(30)));
                        $certs[] = [$ctype, $cname, $from, $cdate];
                        $certRows[] = [
                            'certification_type' => $ctype, 'certification_name' => $cname, 'certification_from' => $from,
                            'certification_date' => $cdate->toDateString(), 'alumnus_id' => $uid,
                            'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
                        ];
                    }
                }
            }

            // "Has a built-in resume" exactly as the app judges it: anything that earns completeness points.
            $builderResume = $summary || $skills || $experiences || $certs || $linkedin;
            $this->alumni[$uid] = [
                'i' => $i, 'name' => "$first $last", 'first' => $first, 'program' => $pid, 'gender' => $gender, 'batch' => $batch,
                'created' => $created, 'active' => $status !== 'deactivated', 'builder' => (bool) $builderResume, 'file' => (bool) $resumeFile,
                'cover' => (bool) $coverFile, 'summary' => $summary, 'skills' => $skills, 'experiences' => $experiences, 'certs' => $certs,
                'employed' => $employed, 'empDate' => $emp['date'], 'firstJob' => $emp['first'],
            ];
        }

        $this->insertChunked('alumnus_skill', $skillRows);
        $this->insertChunked('experiences', $expRows);
        $this->insertChunked('certifications', $certRows);
    }

    private function resumePdf(int $uid, string $name, string $program, string $summary, array $profile): string
    {
        $lines = [[strtoupper($name), 18, true], [$program . ' — Pamantasan ng Lungsod ng Valenzuela', 11], ['Valenzuela City, Metro Manila', 10], ['', 8],
            ['PROFESSIONAL SUMMARY', 12, true], [$summary, 10], ['', 8],
            ['SKILLS', 12, true], [implode(' • ', $this->sample($profile['skills'], 6)), 10], ['', 8],
            ['EXPERIENCE AND PROJECTS', 12, true]];
        foreach ($this->sample($profile['projects'], 2) as $p) $lines[] = ['- ' . $p, 10];
        $lines[] = ['', 8];
        $lines[] = ['EDUCATION', 12, true];
        $lines[] = [$program . ', Pamantasan ng Lungsod ng Valenzuela', 10];
        $path = "alumniDocuments/resumes/seed-resume-{$uid}.pdf";
        $this->disk->put($path, SimplePdf::make($lines));

        return $path;
    }

    private function coverLetterPdf(int $uid, string $name, string $program): string
    {
        $path = "alumniDocuments/coverLetters/seed-cover-{$uid}.pdf";
        $this->disk->put($path, SimplePdf::make([
            [$name, 14, true], ['Valenzuela City', 10], ['', 10], ['Dear Hiring Manager,', 11], ['', 6],
            ["I am a graduate of {$program} from Pamantasan ng Lungsod ng Valenzuela, and I am excited to apply for the open position in your company. During my studies and internship I developed strong technical and communication skills, and I am eager to contribute to your team.", 11],
            ['', 6], ['Thank you for considering my application. I look forward to the opportunity to discuss how I can help your organization.', 11],
            ['', 10], ['Sincerely,', 11], [$name, 11, true],
        ]));

        return $path;
    }

    // ───────────────────────── employers ─────────────────────────

    private function seedEmployers(): void
    {
        $statuses = array_merge(array_fill(0, 73, 'approved'), array_fill(0, 8, 'deactivated'), array_fill(0, 18, 'pending'));
        shuffle($statuses);
        array_unshift($statuses, 'approved');
        $industries = ['Technology' => 22, 'Business & Finance' => 30, 'Healthcare' => 12, 'Education' => 16, 'Government & Public Service' => 6, 'Engineering & Construction' => 14];

        for ($i = 1; $i <= self::EMPLOYERS; $i++) {
            $isDemo = $i === 1;
            $status = $statuses[$i - 1];
            $industry = $isDemo ? 'Technology' : $this->weighted($industries);
            $company = $isDemo ? 'Tech Solutions Inc.' : $this->companyName($industry, true);
            $gender = $isDemo ? 'male' : $this->pick(['male', 'female']);
            [$first, $last, $middle] = $isDemo ? ['Charles', 'Tarog', 'Reyes'] : $this->personName($gender);
            $created = $status === 'pending'
                ? $this->between($this->now->copy()->subDays(25), $this->now->copy()->subHours(6))
                : $this->between($this->launch, $this->now->copy()->subDays(75));
            $approvedData = $status !== 'pending';
            $slug = Str::slug($company);
            $year = mt_rand(1985, 2022);

            $uid = DB::table('users')->insertGetId([
                'user_password' => $this->hash['employer'], 'user_role' => 'employer',
                'user_first_name' => $first, 'user_last_name' => $last, 'user_middle_name' => $this->chance(70) ? $middle : null, 'user_suffix' => null,
                'user_email' => $isDemo ? 'employer@example.com' : "employer{$i}@example.com",
                'user_number' => $this->phone(),
                'user_profile_picture' => $approvedData ? $this->portrait($gender, "employer-$i") : null,
                'user_active' => $status === 'approved' ? 1 : 0,
                'last_active_at' => $status === 'pending' ? null : $this->ts($this->between($created, $status === 'approved' ? $this->now : $created->copy()->addDays(90))),
                'user_muted' => 0, 'must_change_password' => 0,
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
            ]);

            $document = "companyDocuments/seed-{$slug}.pdf";
            $this->disk->put($document, SimplePdf::make([
                ['SAMPLE DOCUMENT — FOR SYSTEM TESTING ONLY', 9, true], ['', 10],
                ['Company Registration Summary', 18, true], ['', 8],
                ['Registered name: ' . $company, 11], ['Industry: ' . $industry, 11], ['Year established: ' . $year, 11],
                ['Business address: ' . $this->address($this->chance(65)), 11], ['Authorized representative: ' . "$first $last", 11],
                ['Reference no.: ' . strtoupper(Str::random(4)) . '-' . mt_rand(100000, 999999), 11], ['', 10],
                ['This file was generated by the PLV-AlumNet seeder to stand in for an uploaded business document.', 9],
            ]));

            DB::table('employers')->insert([
                'user_id' => $uid, 'industry_id' => $this->industryId[$industry] ?? null,
                'employer_position' => $isDemo ? 'HR Manager' : $this->pick(C::CONTACT_POSITIONS),
                'employer_company_name' => $company,
                'employer_company_logo' => $approvedData ? $this->storeImage($this->pools['logo'][$this->poolNext['logo']++ % count($this->pools['logo'])], 'companyLogos', "seed-{$slug}.png") : null,
                'employer_year_established' => $year,
                'employer_website_url' => $this->chance(85) ? 'https://www.' . Str::slug($company, '') . '.com.ph' : null,
                'employer_company_size' => $this->pick([8, 15, 25, 40, 60, 85, 120, 250, 500, 1200, 3000]),
                'employer_company_document' => $document,
                'employer_approved' => $approvedData ? 1 : 0,
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($created),
            ]);

            $addresses = [];
            if ($approvedData) {
                $n = mt_rand(1, 3);
                for ($a = 0; $a < $n; $a++) {
                    $addresses[] = $this->address($a === 0 ? $this->chance(65) : $this->chance(40));
                }
                foreach (array_unique($addresses) as $addr) {
                    DB::table('employer_addresses')->insert(['employer_id' => $uid, 'address' => $addr, 'created_at' => $this->ts($created), 'updated_at' => $this->ts($created)]);
                }
            } else {
                foreach ($this->staffWith('user_management') as $sid) {
                    $this->notify($sid, 'employer_registration_pending', 'New employer registration', "{$company} registered and is waiting for approval.", $uid, $created, 40);
                }
            }

            $this->employers[$uid] = ['i' => $i, 'status' => $status, 'industry' => $industry, 'company' => $company, 'addresses' => $addresses, 'created' => $created, 'name' => "$first $last"];
        }
    }

    // ───────────────────────── job posts ─────────────────────────

    private function programsForIndustry(string $industry): array
    {
        $map = [
            'Healthcare' => ['Bachelor of Science in Psychology', 'Bachelor of Science in Social Work'],
            'Government & Public Service' => ['Bachelor of Public Administration', 'Bachelor of Science in Social Work', 'Bachelor of Science in Psychology'],
            'Technology' => ['Bachelor of Science in Information Technology', 'Bachelor of Science in Information Technology', 'Bachelor of Science in Business Administration Major in Marketing Management', 'Bachelor of Arts in Communication'],
        ];
        $names = $map[$industry] ?? array_keys(array_filter(C::PROGRAMS, fn ($p) => $p['industry'] === $industry));
        $ids = [];
        foreach ($this->programs as $pid => $p) {
            foreach ($names as $n) if ($p['name'] === $n) $ids[] = $pid;
        }
        return $ids ?: array_keys($this->programs);
    }

    private function seedJobs(): void
    {
        foreach ($this->employers as $uid => $e) {
            if ($e['status'] === 'pending') continue;
            $count = $e['i'] === 1 ? 9 : mt_rand(5, 10);
            $this->createJobsFor($uid, $count, $e['company'], $e['industry'], $e['addresses'] ?: [$this->address()], $e['created'], $e['status'] === 'deactivated', false);
        }
        foreach ($this->staff as $uid => $s) {
            $industry = $this->pick(array_keys(C::COMPANY_SUFFIX));
            $company = $this->chance(55) ? 'Pamantasan ng Lungsod ng Valenzuela' : $this->companyName($industry);
            $addr = $company === 'Pamantasan ng Lungsod ng Valenzuela' ? ['Tongco Street, Maysan, Valenzuela City'] : [$this->address()];
            $this->createJobsFor($uid, mt_rand(3, 8), $company, $industry, $addr, $this->launch->copy(), false, true);
        }
    }

    private function createJobsFor(int $ownerId, int $count, string $company, string $industry, array $addresses, Carbon $since, bool $allExpired, bool $staff): void
    {
        $programIds = $staff ? array_keys($this->programs) : $this->programsForIndustry($industry);
        $usedTitles = [];
        for ($n = 0; $n < $count; $n++) {
            $pid = $this->pick($programIds);
            $profile = $this->programs[$pid]['p'];
            $titles = array_values(array_diff($profile['jobs'], $usedTitles)) ?: $profile['jobs'];
            $title = $this->pick($titles);
            $usedTitles[] = $title;

            // Lifecycle: open (60%), closing within a week (10%), expired (30%); a few pending/declined.
            $life = $allExpired ? 'expired' : $this->weighted(['open' => 60, 'expiring' => 10, 'expired' => 30]);
            $review = $staff || $life === 'expired' ? 'approved' : $this->weighted(['approved' => 88, 'pending' => 7, 'declined' => 5]);
            [$posted, $closing] = match ($life) {
                'expired' => [$p = $this->between($this->maxC($since, $this->now->copy()->subDays(540)), $this->now->copy()->subDays(65)), $p->copy()->addDays(mt_rand(30, 60))],
                'expiring' => [$this->between($this->now->copy()->subDays(40), $this->now->copy()->subDays(20)), $this->now->copy()->addDays(mt_rand(0, 6))],
                default => [$this->between($this->now->copy()->subDays(50), $this->now->copy()->subDays(1)), $this->now->copy()->addDays(mt_rand(8, 75))],
            };
            if ($review === 'pending') $posted = $this->between($this->now->copy()->subDays(9), $this->now->copy()->subHours(5));
            if ($posted->lt($since)) $posted = $since->copy()->addDays(mt_rand(1, 20));
            if ($closing->lte($posted)) $closing = $posted->copy()->addDays(mt_rand(30, 60));
            $hiringLimit = $this->weighted([1 => 30, 2 => 30, 3 => 20, 4 => 10, 5 => 10]);

            $type = $this->weighted(['Full-Time' => 70, 'Part-Time' => 20, 'Freelance' => 10]);
            $setup = $this->programs[$pid]['name'] === 'Bachelor of Science in Information Technology'
                ? $this->weighted(['On-Site' => 35, 'Hybrid' => 40, 'Remote' => 25])
                : $this->weighted(['On-Site' => 65, 'Hybrid' => 27, 'Remote' => 8]);
            $cover = $this->jobCover($pid, $title);
            $inline = $this->chance(20) ? $this->descriptionImage($pid) : null;
            $related = array_values(array_filter(array_keys($this->programs), fn ($id) => $id !== $pid && ($this->programs[$id]['college'] === $this->programs[$pid]['college'] || ($this->programs[$id]['p']['industry'] ?? '') === $profile['industry'])));
            $jobPrograms = array_values(array_unique(array_merge([$pid], $this->sample($related, $this->weighted([0 => 45, 1 => 35, 2 => 20])))));
            $declined = $review === 'declined';
            $deletedAt = $declined ? $posted->copy()->addDays(mt_rand(1, 3)) : null;

            $jobId = DB::table('job_postings')->insertGetId([
                'user_id' => $ownerId, 'job_posting_title' => $title, 'job_posting_company' => $company,
                'job_posting_address' => $this->pick($addresses),
                'job_posting_employment_type' => $type, 'job_posting_setup' => $setup,
                'job_posting_description' => $this->jobDescription($title, $company, $type, $setup, $inline),
                'job_posting_date' => $posted->toDateString(), 'job_closing_date' => $closing->toDateString(),
                'hiring_limit' => $hiringLimit,
                'job_posting_image' => $cover, 'job_approved' => $review === 'approved' ? 1 : 0,
                'job_decline_reason' => $declined ? $this->pick(self::DECLINE_REASONS) : null,
                'deleted_at' => $this->ts($deletedAt), 'industry_id' => $this->industryId[$profile['industry']] ?? null,
                'created_at' => $this->ts($posted), 'updated_at' => $this->ts($posted->copy()->addHours(mt_rand(0, 48))),
            ]);

            foreach ($jobPrograms as $jp) {
                DB::table('job_program')->insert(['job_posting_id' => $jobId, 'program_id' => $jp, 'created_at' => $this->ts($posted), 'updated_at' => $this->ts($posted)]);
            }
            foreach ($this->sample($profile['skills'], mt_rand(3, 6)) as $k => $s) {
                if (!isset($this->skillId[$s])) continue;
                DB::table('job_posting_skills')->insert(['job_posting_id' => $jobId, 'skill_id' => $this->skillId[$s], 'is_required' => 1, 'weight' => 1, 'created_at' => $this->ts($posted), 'updated_at' => $this->ts($posted)]);
            }

            if (!$staff) {
                if ($review === 'approved') $this->notify($ownerId, 'job_posting_approved', 'Job posting approved', "Your job posting \"{$title}\" is now live on the job board.", $jobId, $posted->copy()->addHours(mt_rand(2, 30)));
                if ($declined) $this->notify($ownerId, 'job_posting_rejected', 'Job posting rejected', "Your job posting \"{$title}\" was not approved.", $jobId, $deletedAt);
                if ($review === 'pending') {
                    foreach ($this->staffWith('job_management') as $sid) {
                        $this->notify($sid, 'job_posting_submitted', 'New job posting for review', "{$company} submitted \"{$title}\" for approval.", $jobId, $posted, 30);
                    }
                }
            }

            $this->jobs[$jobId] = [
                'owner' => $ownerId, 'staff' => $staff, 'title' => $title, 'company' => $company, 'programs' => $jobPrograms,
                'posted' => $posted, 'closing' => $closing->copy()->endOfDay(), 'review' => $review, 'life' => $life,
                'limit' => $hiringLimit, 'industry' => $this->industryId[$profile['industry']] ?? null,
            ];
        }
    }

    private function jobCover(int $pid, string $title): ?string
    {
        if ($this->chance(10)) return null;   // some posts use the default thumbnail
        $base = base_path('seed-images/jobs');
        $file = "pos-{$pid}-" . Str::slug($title) . '.png';
        if (!is_file("$base/$file")) $file = "course-{$pid}.png";
        if (!is_file("$base/$file")) return null;

        return $this->storeImage("$base/$file", 'jobImages', $file);
    }

    private function descriptionImage(int $pid): ?string
    {
        $candidates = glob(base_path("seed-images/jobs/pos-{$pid}-*.png")) ?: glob(base_path('seed-images/jobs/pos-*.png'));
        if (!$candidates) return null;
        $src = $this->pick($candidates);
        $path = $this->storeImage($src, 'jobDescriptionImages', 'seed-desc-' . basename($src));

        return asset('storage/' . $path);
    }

    private function jobDescription(string $title, string $company, string $type, string $setup, ?string $inlineImage): string
    {
        $cat = C::JOB_CATEGORIES['admin'];
        $catKey = 'admin';
        foreach (C::JOB_CATEGORIES as $key => $c) {
            if (preg_match($c['match'], $title)) { $cat = $c; $catKey = $key; break; }
        }
        $salary = [
            'dev' => [25, 45], 'qa' => [20, 30], 'support' => [18, 28], 'design' => [22, 35], 'accounting' => [20, 30], 'finance' => [25, 40],
            'hr' => [18, 28], 'marketing' => [18, 30], 'teaching' => [18, 28], 'civil' => [25, 40], 'electrical' => [25, 40],
            'health' => [18, 26], 'social' => [20, 30], 'admin' => [16, 24],
        ][$catKey];
        $lo = mt_rand($salary[0], $salary[1] - 4) * 1000;
        $hi = $lo + mt_rand(4, 10) * 1000;
        $pay = match ($type) {
            'Part-Time' => '₱' . number_format((int) ($lo * 0.55)) . ' – ₱' . number_format((int) ($hi * 0.55)) . ' per month (part-time)',
            'Freelance' => 'Project-based rate, depending on scope and experience',
            default => '₱' . number_format($lo) . ' – ₱' . number_format($hi) . ' per month',
        };
        $aboutTpl = mt_rand(0, count(C::COMPANY_ABOUT) - 1);
        $about = match ($aboutTpl) {
            0 => sprintf(C::COMPANY_ABOUT[0], $company, $this->pick(['Valenzuela City', 'Metro Manila', 'Quezon City', 'Bulacan'])),
            1 => sprintf(C::COMPANY_ABOUT[1], mt_rand(1990, 2020), $company),
            default => sprintf(C::COMPANY_ABOUT[$aboutTpl], $company),
        };
        $li = fn (array $items) => '<ul>' . implode('', array_map(fn ($x) => '<li>' . e($x) . '</li>', $items)) . '</ul>';

        $html = '<p><strong>About ' . e($company) . '</strong><br>' . e($about) . '</p>'
            . '<p>' . e(sprintf($cat['intro'], $title)) . '</p>'
            . '<p><strong>Key Responsibilities</strong></p>' . $li($this->sample($cat['resp'], mt_rand(4, 6)))
            . '<p><strong>Qualifications</strong></p>' . $li($this->sample($cat['qual'], mt_rand(3, 5)))
            . '<p><strong>What We Offer</strong></p>' . $li($this->sample(C::BENEFITS, mt_rand(3, 5)))
            . '<p><strong>Work schedule:</strong> ' . e($this->pick(C::SCHEDULES)) . '<br><strong>Salary:</strong> ' . e($pay) . '<br><strong>Work setup:</strong> ' . e($setup) . '</p>';
        if ($inlineImage) {
            $html .= '<p><img src="' . e($inlineImage) . '" alt="Life at ' . e($company) . '"></p>';
        }

        return $html . '<p>Interested applicants may apply directly through PLV-AlumNet. Only shortlisted candidates will be contacted.</p>';
    }

    // ───────────────────────── applications, hires ─────────────────────────

    private function seedApplications(): void
    {
        $eligible = array_keys(array_filter($this->alumni, fn ($a) => $a['builder'] || $a['file']));
        $byProgram = [];
        foreach ($eligible as $aid) $byProgram[$this->alumni[$aid]['program']][] = $aid;
        $demoId = array_key_first($this->alumni);
        $rows = [];
        $taken = [];
        $hires = [];

        // The demo alumnus gets one application in each status on open IT jobs.
        $itJobs = array_keys(array_filter($this->jobs, fn ($j) => $j['review'] === 'approved' && $j['life'] !== 'expired' && in_array($this->alumni[$demoId]['program'], $j['programs'], true)));
        $forced = [];
        foreach (array_slice($itJobs, 0, 6) as $k => $jid) {
            $forced[$jid] = ['pending', 'shortlisted', 'declined', 'hired', 'pending', 'shortlisted'][$k];
        }

        foreach ($this->jobs as $jid => $job) {
            if ($job['review'] !== 'approved') continue;
            $end = $this->minC($job['closing'], $this->now);
            $want = mt_rand(5, 20);
            // Only alumni who already had an account before the post closed can apply.
            $registered = fn ($aid) => $this->alumni[$aid]['created']->lt($end);
            $preferred = [];
            foreach ($job['programs'] as $pid) $preferred = array_merge($preferred, $byProgram[$pid] ?? []);
            $preferred = array_values(array_filter($preferred, $registered));
            $pool = array_values(array_filter($eligible, $registered));
            $chosen = isset($forced[$jid]) ? [$demoId] : [];
            foreach ($this->sample($preferred, (int) round($want * 0.7)) as $aid) $chosen[] = $aid;
            foreach ($this->sample($pool, $want * 2) as $aid) {
                if (count(array_unique($chosen)) >= $want) break;
                $chosen[] = $aid;
            }
            $chosen = array_values(array_unique($chosen));
            shuffle($chosen);

            $limit = (int) $job['limit'];
            $hireTarget = $job['life'] === 'expired' ? mt_rand(0, $limit) : ($this->chance(50) ? 0 : mt_rand(0, max(0, $limit - 1)));
            $hired = 0;
            foreach ($chosen as $aid) {
                if (isset($taken["$jid-$aid"])) continue;
                $taken["$jid-$aid"] = true;
                $a = $this->alumni[$aid];
                $applied = $this->between($this->maxC($job['posted'], $a['created']), $end);

                if ($aid === $demoId && isset($forced[$jid])) {
                    $status = $forced[$jid];
                } elseif ($hired < $hireTarget) {
                    $status = 'hired';
                } elseif ($job['life'] === 'expired') {
                    $status = 'declined';
                } else {
                    $status = $this->weighted(['pending' => 50, 'shortlisted' => 22, 'declined' => 28]);
                }
                if ($status === 'hired') $hired++;
                $statusAt = $status === 'pending' ? $applied : $this->between($applied->copy()->addDays(2), $this->minC($applied->copy()->addDays(35), $this->now));
                $hiredAt = $status === 'hired' ? $statusAt : null;

                $source = $a['builder'] && $a['file'] ? ($this->chance(60) ? 'builder' : 'profile') : ($a['builder'] ? 'builder' : 'profile');
                $rows[] = [
                    'job_id' => $jid, 'alumnus_id' => $aid, 'application_date' => $this->ts($applied), 'application_status' => $status,
                    'hired_at' => $this->ts($hiredAt), 'application_score' => null,
                    'resume_source' => $source, 'resume_path' => null,
                    'cover_letter_source' => $a['cover'] && $this->chance(50) ? 'profile' : 'none', 'cover_letter_path' => null,
                    'builder_resume_snapshot' => $source === 'builder' ? json_encode($this->snapshot($a)) : null,
                    'is_read' => $status !== 'pending' || $this->chance(55) ? 1 : 0,
                    'created_at' => $this->ts($applied), 'updated_at' => $this->ts($statusAt),
                ];
                $this->applications[] = [$jid, $aid, $status];

                if ($status !== 'pending') {
                    $label = ['hired' => 'Congratulations! You’re hired', 'shortlisted' => 'You’ve been shortlisted', 'declined' => 'Application update'][$status];
                    $body = match ($status) {
                        'hired' => "{$job['company']} hired you for \"{$job['title']}\".",
                        'shortlisted' => "{$job['company']} shortlisted your application for \"{$job['title']}\".",
                        default => "Your application for \"{$job['title']}\" at {$job['company']} was not selected this time.",
                    };
                    $this->notify($aid, "job_application_{$status}", $label, $body, $jid, $statusAt);
                }
                if ($status === 'hired' && (!isset($hires[$aid]) || $hiredAt->gt($hires[$aid]['at']))) {
                    $hires[$aid] = ['at' => $hiredAt, 'job' => $job];
                }
            }
        }
        $this->insertChunked('job_applications', $rows);

        // A hire through the platform becomes the alumnus's current job.
        foreach ($hires as $aid => $h) {
            $a = $this->alumni[$aid];
            DB::table('alumni')->where('user_id', $aid)->update([
                'alumnus_employment_status' => 1, 'alumnus_employed_via_platform' => 1,
                'alumnus_workplace' => $h['job']['company'], 'alumnus_workplace_undisclosed' => 0,
                'alumnus_job_position' => $h['job']['title'], 'industry_id' => $h['job']['industry'],
                'alumnus_employment_date' => $h['at']->toDateString(),
                'alumnus_first_job_date' => ($a['firstJob'] && $a['firstJob']->lt($h['at']) ? $a['firstJob'] : $h['at'])->toDateString(),
            ]);
            $this->alumni[$aid]['employed'] = true;
        }
    }

    private function snapshot(array $a): array
    {
        return [
            'summary' => $a['summary'] ?? '',
            'skills' => array_map(fn ($s) => ['name' => $s], $a['skills']),
            'experiences' => array_map(fn ($e) => ['type' => $e[0], 'job_title' => $e[1], 'job_description' => $e[2], 'duration_months' => $e[5]], $a['experiences']),
            'certifications' => array_map(fn ($c) => ['certification_type' => $c[0], 'certification_name' => $c[1], 'certification_from' => $c[2], 'certification_date' => $c[3]->toDateString()], $a['certs']),
        ];
    }

    // ───────────────────────── bookmarks, votes, reviews ─────────────────────────

    private function seedEngagement(): void
    {
        $openJobs = array_keys(array_filter($this->jobs, fn ($j) => $j['review'] === 'approved' && $j['life'] !== 'expired'));
        $bookmarks = [];
        foreach ($this->alumni as $aid => $a) {
            if (!$a['active'] || !$this->chance($a['i'] === 1 ? 100 : 35)) continue;
            foreach ($this->sample($openJobs, mt_rand(1, 6)) as $jid) {
                $at = $this->between($this->maxC($this->jobs[$jid]['posted'], $a['created']), $this->now);
                $bookmarks["$jid-$aid"] = ['job_id' => $jid, 'alumnus_id' => $aid, 'created_at' => $this->ts($at), 'updated_at' => $this->ts($at)];
            }
        }
        $this->insertChunked('job_bookmarks', array_values($bookmarks));

        $votes = [];
        $reviews = [];
        foreach ($this->applications as [$jid, $aid, $status]) {
            $job = $this->jobs[$jid];
            if ($this->chance(40)) {
                $at = $this->between($job['posted'], $this->now);
                $votes["$jid-$aid"] = ['job_posting_id' => $jid, 'alumnus_id' => $aid, 'vote' => $this->chance(80) ? 'upvote' : 'downvote', 'created_at' => $this->ts($at), 'updated_at' => $this->ts($at)];
            }
            if ($status === 'hired' && !$job['staff'] && $this->chance(60) && !isset($reviews[$job['owner'] . '-' . $aid])) {
                $rating = (int) $this->weighted([5 => 35, 4 => 35, 3 => 18, 2 => 8, 1 => 4]);
                $at = $this->between($job['posted']->copy()->addDays(30), $this->now);
                $reviews[$job['owner'] . '-' . $aid] = [
                    'employer_id' => $job['owner'], 'alumnus_id' => $aid, 'rating' => $rating,
                    'review_body' => $this->chance(80) ? $this->pick(C::REVIEWS[$rating]) : null,
                    'created_at' => $this->ts($at), 'updated_at' => $this->ts($at),
                ];
            }
        }
        $this->insertChunked('job_posting_votes', array_values($votes));
        $this->insertChunked('employer_reviews', array_values($reviews));
    }

    // ───────────────────────── alumni IDs and yearbooks ─────────────────────────

    private function seedIdsAndYearbooks(): void
    {
        $staffIds = array_keys($this->staff);
        $rr = 0;
        $year = (int) $this->now->year;
        $ids = $books = [];
        foreach ($this->alumni as $aid => $a) {
            $age = $year - (int) $a['batch']->year;
            if ($a['i'] === 1 || $this->chance(90)) {
                $status = $a['i'] === 1 ? 'ready_to_claim' : ($age >= 2 ? $this->weighted(['claimed' => 70, 'ready_to_claim' => 20, 'pending' => 10]) : ($age === 1 ? $this->weighted(['claimed' => 40, 'ready_to_claim' => 35, 'pending' => 25]) : $this->weighted(['pending' => 50, 'ready_to_claim' => 35, 'claimed' => 15])));
                $updated = $this->between($a['created'], $this->now);
                $ids[] = [
                    'alumnus_id' => $aid, 'status' => $status, 'claim_location' => 'University Registrar',
                    'status_updated_at' => $this->ts($updated), 'updated_by' => $staffIds[$rr++ % count($staffIds)],
                    'created_at' => $this->ts($a['created']), 'updated_at' => $this->ts($updated),
                ];
            }
            if ($a['i'] === 1 || $this->chance(85)) {
                $onHand = $age >= 1 || $this->chance(40);
                $claiming = !$onHand ? 'pending' : ($age >= 2 ? $this->weighted(['claimed' => 65, 'ready_to_claim' => 30, 'pending' => 5]) : $this->weighted(['claimed' => 35, 'ready_to_claim' => 50, 'pending' => 15]));
                $updated = $this->between($a['created'], $this->now);
                $books[] = [
                    'alumnus_id' => $aid, 'distribution_status' => $onHand ? 'on_hand' : 'pending',
                    'distribution_scheduled_at' => $onHand ? $this->ts($a['batch']->copy()->addMonths(mt_rand(4, 7))->setTime(9, 0)) : null,
                    'distribution_building' => 'Student Building', 'distribution_room_floor' => 'Office of Student Affairs, 2nd Floor',
                    'claiming_status' => $a['i'] === 1 ? 'claimed' : $claiming,
                    'status_updated_at' => $this->ts($updated), 'updated_by' => $staffIds[$rr++ % count($staffIds)],
                    'created_at' => $this->ts($a['created']), 'updated_at' => $this->ts($updated),
                ];
            }
        }
        $this->insertChunked('alumni_ids', $ids);
        $this->insertChunked('alumni_yearbooks', $books);
    }

    // ───────────────────────── notices ─────────────────────────

    private function seedNotices(): void
    {
        $creators = $this->staffWith('notices');
        $thumbs = [
            'event' => glob(base_path('seed-images/notices/notice-event-*.png')),
            'seminar' => array_merge(glob(base_path('seed-images/notices/notice-seminar-*.png')), glob(base_path('seed-images/notices/notice-workshop-*.png'))),
            'announcement' => glob(base_path('seed-images/notices/notice-announce-*.png')),
        ];
        $activeAlumni = array_keys(array_filter($this->alumni, fn ($a) => $a['active']));
        $notices = [];
        $c = 0;

        foreach (C::NOTICE_EVENTS as $k => [$title, $location, $intro, $points]) {
            $at = $k % 2 === 0 ? $this->between($this->now->copy()->addDays(5), $this->now->copy()->addDays(120)) : $this->between($this->now->copy()->subDays(420), $this->now->copy()->subDays(10));
            $at->setTime($this->pick([8, 9, 13, 16, 17]), 0);
            $html = '<p>' . e($intro) . '</p><p><strong>What to expect:</strong></p><ul>' . implode('', array_map(fn ($p) => '<li>' . e($p) . '</li>', $points)) . '</ul><p><strong>Venue:</strong> ' . e($location) . '</p>';
            $notices[] = ['event', $title, $at, $location, $html, $this->weighted(['everyone' => 60, 'alumni' => 40]), null, null];
        }
        foreach (C::NOTICE_SEMINARS as $k => [$title, $speaker, $topic, $intro]) {
            $at = $k % 2 === 0 ? $this->between($this->now->copy()->addDays(7), $this->now->copy()->addDays(110)) : $this->between($this->now->copy()->subDays(400), $this->now->copy()->subDays(12));
            $at->setTime($this->pick([9, 13, 14]), 0);
            $html = '<p>' . e($intro) . '</p><p><strong>Speaker:</strong> ' . e($speaker) . '</p><p><strong>Who should attend:</strong> PLV alumni and graduating students. Certificates of attendance will be given.</p><p><strong>Venue:</strong> COED Assembly Hall</p>';
            $notices[] = ['seminar', $title, $at, 'COED Assembly Hall', $html, $this->weighted(['alumni' => 70, 'everyone' => 30]), $speaker, $topic];
        }
        foreach (C::NOTICE_ANNOUNCEMENTS as $k => [$title, $html]) {
            $at = $this->between($this->now->copy()->subDays(300), $this->now->copy()->subDays(1));
            $recipient = ['alumni', 'alumni', 'everyone', 'alumni', 'everyone', 'alumni', 'alumni', 'everyone'][$k] ?? 'everyone';
            $notices[] = ['announcement', $title, $at, null, $html, $recipient, null, null];
        }

        $recentForNotify = [];
        foreach ($notices as $n => [$cat, $title, $at, $location, $html, $recipient, $speaker, $topic]) {
            $createdAt = $cat === 'announcement' ? $at->copy() : $this->minC($this->now->copy()->subHours(3), $at->copy()->subDays(mt_rand(14, 45)));
            $thumb = $thumbs[$cat] && $this->chance(85) ? $this->storeImage($this->pick($thumbs[$cat]), 'noticeThumbnails', 'seed-' . Str::slug($title) . '.png') : null;
            $nid = DB::table('notices')->insertGetId([
                'category' => $cat, 'title' => $title, 'thumbnail' => $thumb,
                // Announcements keep their posting date here too (the list sorts by it) — same as NoticeController::preparedData().
                'event_datetime' => $this->ts($at),
                'location' => $location, 'description' => $html, 'recipient' => $recipient,
                'speaker_name' => $speaker, 'speaker_topic' => $topic,
                'created_by' => $creators[$c++ % count($creators)],
                'created_at' => $this->ts($createdAt), 'updated_at' => $this->ts($createdAt),
            ]);
            if ($cat !== 'announcement' && $recipient !== 'employer') {
                $interested = $this->sample($activeAlumni, mt_rand(20, 160));
                if ($n < 3) $interested[] = array_key_first($this->alumni);
                $rows = [];
                foreach (array_unique($interested) as $aid) {
                    $t = $this->ts($this->between($this->maxC($createdAt, $this->alumni[$aid]['created']), $this->minC($at, $this->now)));
                    $rows[] = ['notice_id' => $nid, 'alumnus_id' => $aid, 'created_at' => $t, 'updated_at' => $t];
                }
                $this->insertChunked('notice_interests', $rows);
            }
            $recentForNotify[] = [$createdAt, $nid, $cat, $title, $recipient];
        }

        // The two newest notices of each kind notified their recipients, like the real "create notice" flow.
        usort($recentForNotify, fn ($x, $y) => $y[0] <=> $x[0]);
        $employerIds = array_keys(array_filter($this->employers, fn ($e) => $e['status'] === 'approved'));
        $newest = [];
        foreach ($recentForNotify as $row) {
            if (count(array_filter($newest, fn ($r) => $r[2] === $row[2])) < 2) $newest[] = $row;
        }
        foreach ($newest as [$at, $nid, $cat, $title, $recipient]) {
            $targets = array_merge($recipient !== 'employer' ? array_keys($this->alumni) : [], $recipient !== 'alumni' ? $employerIds : []);
            $label = ['event' => 'New event', 'seminar' => 'New seminar', 'announcement' => 'New announcement'][$cat];
            foreach ($targets as $uid) $this->notify($uid, "new_{$cat}", $label, $title, $nid, $at, 55);
        }
    }

    // ───────────────────────── testimonials ─────────────────────────

    private function seedTestimonials(): void
    {
        $authors = array_keys(array_filter($this->alumni, fn ($a) => $a['active']));
        $authors = array_values(array_unique(array_merge([array_key_first($this->alumni)], $this->sample($authors, 179))));
        $rows = [];
        foreach ($authors as $aid) {
            $a = $this->alumni[$aid];
            $at = $this->between($a['created'], $this->now);
            $posted = $a['i'] === 1 || $this->chance(70);
            $rows[] = [
                'user_id' => $aid, 'testimonial_body' => sprintf($this->pick(C::TESTIMONIALS), $this->shortProgram($this->programs[$a['program']]['name'])),
                'testimonial_post' => $posted ? 1 : 0, 'deleted_at' => !$posted && $this->chance(8) ? $this->ts($at->copy()->addDays(3)) : null,
                'created_at' => $this->ts($at), 'updated_at' => $this->ts($at),
            ];
            if (!$posted) {
                foreach ($this->staffWith('testimonials') as $sid) $this->notify($sid, 'testimonial_submitted', 'New testimonial', "{$a['name']} submitted a testimonial for review.", null, $at, 40);
            }
        }
        $this->insertChunked('testimonials', $rows);
    }

    // ───────────────────────── messages and flags ─────────────────────────

    private function seedMessages(): void
    {
        $active = array_keys(array_filter($this->alumni, fn ($a) => $a['active']));
        $byProgram = [];
        foreach ($active as $aid) $byProgram[$this->alumni[$aid]['program']][] = $aid;
        $demo = array_key_first($this->alumni);
        $pairs = [];
        for ($k = 0; $k < 650; $k++) {
            $a = $k < 8 ? $demo : $this->pick($active);
            $pool = $this->chance(60) ? ($byProgram[$this->alumni[$a]['program']] ?? $active) : $active;
            $b = $this->pick($pool);
            if ($a === $b) continue;
            $pairs[min($a, $b) . '-' . max($a, $b)] = [$a, $b];
        }

        $reviewers = $this->staffWith('messaging');
        $flagIdx = 0;
        foreach (array_values($pairs) as $n => [$a, $b]) {
            $script = $this->pick(C::CONVERSATIONS);
            $start = $this->between($this->maxC($this->alumni[$a]['created'], $this->alumni[$b]['created']), $this->now->copy()->subHours(2));
            $fill = [$this->pick(['Bluewave Digital Solutions', 'a BPO in Quezon City', 'the city hall', 'a private school', 'an accounting firm', 'a construction company']), $this->shortProgram($this->programs[$this->alumni[$a]['program']]['name'])];
            $convId = DB::table('conversations')->insertGetId([
                'conversation_user_a' => $a, 'conversation_user_b' => $b,
                'conversation_last_message_at' => $this->ts($start), 'conversation_created_at' => $this->ts($start),
                'created_at' => $this->ts($start), 'updated_at' => $this->ts($start),
            ]);
            $t = $start->copy();
            $lines = $script;
            $flagReason = null;
            if ($n % 14 === 5 && $flagIdx < 46) {
                $flagReason = C::FLAGGED ? array_keys(C::FLAGGED)[$flagIdx % 3] : null;
                $lines[] = ['a', $this->pick(C::FLAGGED[$flagReason]), true];
                $flagIdx++;
            }
            foreach ($lines as $idx => $line) {
                $from = $line[0] === 'a' ? $a : $b;
                $to = $from === $a ? $b : $a;
                $text = str_contains($line[1], '%s') ? sprintf($line[1], $fill[$idx % 2]) : $line[1];
                $isLast = $idx === count($lines) - 1;
                $read = !($isLast && $t->gt($this->now->copy()->subDays(3)) && $this->chance(60));
                $mid = DB::table('messages')->insertGetId([
                    'conversation_id' => $convId, 'sender_id' => $from, 'receiver_id' => $to, 'message_content' => $text,
                    'attachment_path' => null, 'attachment_original_name' => null, 'attachment_mime_type' => null,
                    'message_read' => $read ? 1 : 0, 'message_created_at' => $this->ts($t), 'created_at' => $this->ts($t), 'updated_at' => $this->ts($t),
                ]);
                if (!empty($line[2]) && $flagReason) {
                    $status = $this->weighted(['pending' => 40, 'warned' => 25, 'muted' => 15, 'dismissed' => 20]);
                    $reviewedAt = $status === 'pending' ? null : $this->minC($this->now, $t->copy()->addHours(mt_rand(1, 72)));
                    DB::table('message_flags')->insert([
                        'message_id' => $mid, 'flag_reasons' => json_encode([$flagReason]), 'status' => $status,
                        'reviewed_by' => $status === 'pending' ? null : $this->pick($reviewers), 'reviewed_at' => $this->ts($reviewedAt),
                        'created_at' => $this->ts($t), 'updated_at' => $this->ts($reviewedAt ?? $t),
                    ]);
                    if ($status === 'muted') DB::table('users')->where('user_id', $from)->update(['user_muted' => 1]);
                }
                $last = $t->copy();
                $t->addMinutes(mt_rand(2, 240));
                if ($t->gt($this->now)) $t = $this->now->copy()->subMinutes(mt_rand(1, 30));
            }
            DB::table('conversations')->where('conversation_id', $convId)->update(['conversation_last_message_at' => $this->ts($last), 'updated_at' => $this->ts($last)]);
        }
    }

    // ───────────────────────── chatbot ─────────────────────────

    private function seedChatbot(): void
    {
        $agents = $this->staffWith('messaging');
        $rr = 0;
        $activeAlumni = array_keys(array_filter($this->alumni, fn ($a) => $a['active']));
        $users = array_merge(
            array_map(fn ($id) => [$id, 'alumni'], array_values(array_unique(array_merge([array_key_first($this->alumni)], $this->sample($activeAlumni, 169))))),
            array_map(fn ($id) => [$id, 'employer'], array_values(array_unique(array_merge([array_key_first($this->employers)], $this->sample(array_keys(array_filter($this->employers, fn ($e) => $e['status'] === 'approved')), 29)))))
        );

        foreach ($users as [$uid, $kind]) {
            [$q, $answer, $needsAgent, $agentReply] = $this->pick($kind === 'alumni' ? C::CHAT_ALUMNI : C::CHAT_EMPLOYER);
            $since = $kind === 'alumni' ? $this->alumni[$uid]['created'] : $this->employers[$uid]['created'];
            $created = $this->between($since, $this->now->copy()->subHours(1));
            $status = $needsAgent ? $this->weighted(['waiting_agent' => 25, 'with_agent' => 30, 'resolved' => 45]) : 'ai_active';
            // Every agent has been assigned tickets; only some of them have resolved one yet.
            $resolvers = array_slice($agents, 0, max(1, (int) ceil(count($agents) / 2)));
            $agentUid = match ($status) {
                'with_agent' => $agents[$rr++ % count($agents)],
                'resolved' => $resolvers[$rr++ % count($resolvers)],
                default => null,
            };
            $escalated = $needsAgent ? $created->copy()->addMinutes(mt_rand(2, 10)) : null;
            $claimed = $agentUid ? $this->minC($this->now, $escalated->copy()->addMinutes(mt_rand(5, 180))) : null;
            $resolved = $status === 'resolved' ? $this->minC($this->now, $claimed->copy()->addMinutes(mt_rand(10, 600))) : null;

            $tid = DB::table('chat_tickets')->insertGetId([
                'user_id' => $uid, 'office_id' => $agentUid ? $this->staff[$agentUid]['office_id'] : null, 'status' => $status,
                'failed_attempts' => $needsAgent ? mt_rand(1, 2) : 0, 'escalated_at' => $this->ts($escalated),
                'claimed_at' => $this->ts($claimed), 'resolved_at' => $this->ts($resolved),
                'created_at' => $this->ts($created), 'updated_at' => $this->ts($resolved ?? $claimed ?? $escalated ?? $created),
            ]);
            $msgs = [['user', $uid, $q, $created], ['ai', null, $answer, $created->copy()->addSeconds(4)]];
            if ($needsAgent) {
                $msgs[] = ['user', $uid, $this->pick(['Can I talk to a staff member?', 'I need help from a person please.', 'Pwede po ba makausap ang staff?']), $escalated];
                $msgs[] = ['ai', null, 'I’m connecting you to a staff member. Please wait — someone from the Alumni Affairs Office will reply shortly.', $escalated->copy()->addSeconds(3)];
            }
            if ($agentUid) $msgs[] = ['agent', $agentUid, $agentReply, $claimed->copy()->addMinutes(1)];
            if ($resolved) {
                $msgs[] = ['user', $uid, $this->pick(['Thank you po!', 'Got it, thanks a lot!', 'Salamat po sa tulong.']), $resolved->copy()->subMinutes(2)];
                $msgs[] = ['agent', $agentUid, 'You’re welcome! I’ll mark this conversation as resolved. Have a great day!', $resolved];
            }
            $rows = [];
            foreach ($msgs as [$type, $sender, $text, $at]) {
                $rows[] = ['ticket_id' => $tid, 'sender_type' => $type, 'sender_id' => $sender, 'message' => $text, 'created_at' => $this->ts($at), 'updated_at' => $this->ts($at)];
            }
            DB::table('chat_ticket_messages')->insert($rows);
            if ($status === 'waiting_agent') {
                foreach ($agents as $sid) $this->notify($sid, 'live_agent_escalation', 'Chat needs an agent', 'A user is waiting for a live agent in the chatbot.', $tid, $escalated, 30);
            }
        }
    }

    // ───────────────────────── finishing ─────────────────────────

    private function flushNotifications(): void
    {
        $this->insertChunked('user_notifications', $this->notifications, 1000);
        $this->notifications = [];
    }

    /** Resume completeness via the app's own scoring, and match scores via JobMatchService. */
    private function finalize(): void
    {
        Alumnus::with(['skills', 'experiences', 'certifications'])->chunkById(200, function ($chunk) {
            foreach ($chunk as $alumnus) $alumnus->refreshResumeCompleteness();
        }, 'user_id');

        $service = app(JobMatchService::class);
        $jobs = JobPosting::withTrashed()->with(['skills', 'programs', 'employer'])->whereIn('job_posting_id', array_unique(array_column($this->applications, 0)))->get()->keyBy('job_posting_id');
        $alumni = Alumnus::with(['skills', 'experiences', 'certifications', 'program'])->whereIn('user_id', array_unique(array_column($this->applications, 1)))->get()->keyBy('user_id');
        foreach ($this->applications as [$jid, $aid]) {
            if (!isset($jobs[$jid], $alumni[$aid])) continue;
            $match = $service->scoreFor($jobs[$jid], $alumni[$aid]);
            DB::table('job_applications')->where('job_id', $jid)->where('alumnus_id', $aid)->update(['application_score' => $match->score]);
        }
    }
}
