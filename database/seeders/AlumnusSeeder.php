<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Program;
use App\Models\Section;
use App\Models\Skill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlumnusSeeder extends Seeder
{
    /** Same weighting as AlumniPracticeSeeder so both alumni pools look consistent. */
    private const ALUMNI_ID_STATUS_POOL = [
        'pending', 'pending', 'pending', 'pending',
        'under_review', 'under_review', 'under_review',
        'ready_to_claim', 'ready_to_claim',
        'claimed', 'claimed',
    ];

    /**
     * Practice dataset spanning several programs so the job-matching /
     * ranking feature (JobMatchService) has real variety to score against
     * instead of a single alumnus. Employment fields are deliberately mixed
     * (employed/undisclosed/unemployed-but-with-a-past-first-job) so every
     * branch of the alumni profile page has something to show.
     */
    public function run(): void
    {
        $staffIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');

        $alumni = [
            [
                'first' => 'Ryza', 'last' => 'Ison', 'gender' => 'female',
                'program' => 'Bachelor of Science in Information Technology', 'section' => '4-2', 'batch' => 2023,
                'email' => 'alumni@example.com',
                'skills' => ['PHP', 'Laravel', 'JavaScript', 'Git & GitHub'],
                'experience' => ['title' => 'Junior Web Developer', 'months' => 8, 'industry' => 'Technology'],
                'certification' => ['name' => 'AWS Cloud Practitioner', 'from' => 'Amazon Web Services'],
                'summary' => 'Aspiring full stack developer with hands-on experience building web applications with Laravel and JavaScript.',
            ],
            [
                'first' => 'Miguel', 'last' => 'Torres', 'gender' => 'male',
                'program' => 'Bachelor of Science in Information Technology', 'section' => '4-3', 'batch' => 2024,
                'email' => 'alumni2@example.com',
                'skills' => ['JavaScript', 'React', 'HTML & CSS', 'Figma'],
                'experience' => ['title' => 'Web Development Intern', 'months' => 4, 'industry' => 'Technology'],
                'certification' => null,
                'summary' => 'Front-end focused IT graduate who enjoys turning designs into responsive interfaces.',
            ],
            [
                'first' => 'Angela', 'last' => 'Cruz', 'gender' => 'female',
                'program' => 'Bachelor of Science in Information Technology', 'section' => '4-4', 'batch' => 2022,
                'email' => 'alumni3@example.com',
                'skills' => ['PHP', 'MySQL', 'Network Administration'],
                'experience' => null,
                'certification' => ['name' => 'CCNA', 'from' => 'Cisco'],
                'summary' => 'Backend-leaning developer with a growing interest in network administration.',
            ],
            [
                'first' => 'Kevin', 'last' => 'Santos', 'gender' => 'male',
                'program' => 'Bachelor of Science in Electrical Engineering', 'section' => '4-5', 'batch' => 2023,
                'email' => 'alumni4@example.com',
                'skills' => ['Circuit Design', 'AutoCAD', 'Network Administration'],
                'experience' => ['title' => 'Electrical Technician', 'months' => 12, 'industry' => 'Technology'],
                'certification' => null,
                'summary' => 'Electrical engineering graduate with field experience in circuit design and maintenance.',
                // Was employed before, now isn't — exercises "first job date
                // still shows after going back to unemployed" on the profile.
                'past_first_job' => true,
            ],
            [
                'first' => 'Bianca', 'last' => 'Mendoza', 'gender' => 'female',
                'program' => 'Bachelor of Science in Psychology', 'section' => '4-6', 'batch' => 2021,
                'email' => 'alumni5@example.com',
                'skills' => ['Psychological Assessment', 'Counseling', 'Communication'],
                'experience' => ['title' => 'Guidance Counselor Assistant', 'months' => 18, 'industry' => 'Healthcare'],
                'certification' => ['name' => 'Basic Life Support', 'from' => 'Philippine Red Cross'],
                'summary' => 'Psychology graduate experienced in student counseling and psychological first aid.',
            ],
            [
                'first' => 'Carlo', 'last' => 'Villanueva', 'gender' => 'male',
                'program' => 'Bachelor of Science in Social Work', 'section' => '4-7', 'batch' => 2022,
                'email' => 'alumni6@example.com',
                'skills' => ['Case Management', 'Community Organizing', 'Communication'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Social work graduate passionate about community development programs.',
            ],
            [
                'first' => 'Diana', 'last' => 'Ramos', 'gender' => 'female',
                'program' => 'Bachelor of Science in Business Administration Major in Human Resource Management', 'section' => '4-8', 'batch' => 2023,
                'email' => 'alumni7@example.com',
                'skills' => ['Recruitment & Selection', 'Payroll Management', 'Communication', 'Microsoft Excel'],
                'experience' => ['title' => 'HR Assistant', 'months' => 10, 'industry' => 'Business & Finance'],
                'certification' => ['name' => 'HR Professional Certification', 'from' => 'PMAP'],
                'summary' => 'HR graduate with hands-on recruitment and payroll processing experience.',
                // Currently employed but chose not to name the employer —
                // exercises the "prefer not to disclose" workplace toggle.
                'workplace_undisclosed' => true,
            ],
            [
                'first' => 'Ella', 'last' => 'Fernandez', 'gender' => 'prefer_not_to_say',
                'program' => 'Bachelor of Science in Business Administration Major in Human Resource Management', 'section' => '4-9', 'batch' => 2024,
                'email' => 'alumni8@example.com',
                'skills' => ['Communication', 'Teamwork'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Recent HR graduate eager to start a career in people operations.',
            ],
            [
                'first' => 'Francis', 'last' => 'Aquino', 'gender' => 'male',
                'program' => 'Bachelor of Early Childhood Education', 'section' => '4-10', 'batch' => 2022,
                'email' => 'alumni9@example.com',
                'skills' => ['Lesson Planning', 'Classroom Management', 'Communication'],
                'experience' => ['title' => 'Student Teacher', 'months' => 6, 'industry' => 'Education'],
                'certification' => ['name' => 'Licensure Examination for Teachers', 'from' => 'PRC'],
                'summary' => 'Early childhood education graduate with a passion for early learners.',
            ],
            [
                'first' => 'Grace', 'last' => 'Lim', 'gender' => 'female',
                'program' => 'Bachelor of Secondary Education Major in Mathematics', 'section' => '4-11', 'batch' => 2023,
                'email' => 'alumni10@example.com',
                'skills' => ['Lesson Planning', 'Classroom Management', 'Critical Thinking'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Math education graduate looking to bring engaging lessons to the classroom.',
            ],
            // Second wave — rounds out program coverage to all 17 programs
            // and, together with the first wave, gives "Customer Success
            // Associate" in JobSeeder a 14-applicant pool so the top-10
            // ranking badge on the employer's applicants page has both
            // ranked and unranked rows to show.
            [
                'first' => 'Nathaniel', 'last' => 'Bautista', 'gender' => 'male',
                'program' => 'Bachelor of Arts in Communication', 'section' => '4-12', 'batch' => 2023,
                'email' => 'alumni11@example.com',
                'skills' => ['Content Writing', 'Public Speaking', 'Communication'],
                'experience' => ['title' => 'Media Production Assistant', 'months' => 6, 'industry' => 'Education'],
                'certification' => null,
                'summary' => 'Communication graduate with a knack for content writing and public speaking.',
            ],
            [
                'first' => 'Samantha', 'last' => 'Delos Reyes', 'gender' => 'female',
                'program' => 'Bachelor of Science in Accountancy', 'section' => '4-13', 'batch' => 2022,
                'email' => 'alumni12@example.com',
                'skills' => ['Financial Auditing', 'Bookkeeping', 'Tax Preparation', 'Microsoft Excel'],
                'experience' => ['title' => 'Audit Assistant', 'months' => 14, 'industry' => 'Business & Finance'],
                'certification' => ['name' => 'Certified Public Accountant (Board Passer)', 'from' => 'PRC'],
                'summary' => 'Accountancy graduate with audit and bookkeeping experience across small business clients.',
            ],
            [
                'first' => 'Joshua', 'last' => 'Manalo', 'gender' => 'male',
                'program' => 'Bachelor of Science in Business Administration Major in Financial Management', 'section' => '4-14', 'batch' => 2023,
                'email' => 'alumni13@example.com',
                'skills' => ['Financial Modeling', 'Microsoft Excel', 'Critical Thinking'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Financial management graduate comfortable building models and forecasts in Excel.',
            ],
            [
                'first' => 'Camille', 'last' => 'Ocampo', 'gender' => 'female',
                'program' => 'Bachelor of Science in Business Administration Major in Marketing Management', 'section' => '4-15', 'batch' => 2024,
                'email' => 'alumni14@example.com',
                'skills' => ['Digital Marketing', 'Market Research', 'Content Writing', 'Canva'],
                'experience' => ['title' => 'Marketing Intern', 'months' => 5, 'industry' => 'Business & Finance'],
                'certification' => null,
                'summary' => 'Marketing management graduate with hands-on digital campaign experience.',
            ],
            [
                'first' => 'Rico', 'last' => 'Navarro', 'gender' => 'male',
                'program' => 'Bachelor of Science in Civil Engineering', 'section' => '4-1', 'batch' => 2022,
                'email' => 'alumni15@example.com',
                'skills' => ['Project Estimation', 'AutoCAD', 'Structural Analysis'],
                'experience' => ['title' => 'Site Engineer Intern', 'months' => 9, 'industry' => 'Engineering & Construction'],
                'certification' => null,
                'summary' => 'Civil engineering graduate with site supervision and project estimation experience.',
            ],
            [
                'first' => 'Patricia', 'last' => 'Alonzo', 'gender' => 'female',
                'program' => 'Bachelor of Public Administration', 'section' => '4-2', 'batch' => 2023,
                'email' => 'alumni16@example.com',
                'skills' => ['Policy Analysis', 'Public Administration', 'Community Organizing'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Public administration graduate interested in local government policy work.',
            ],
            [
                'first' => 'Daniel', 'last' => 'Castro', 'gender' => 'male',
                'program' => 'Bachelor of Secondary Education Major in English', 'section' => '4-3', 'batch' => 2022,
                'email' => 'alumni17@example.com',
                'skills' => ['Lesson Planning', 'Classroom Management', 'Communication'],
                'experience' => ['title' => 'Student Teacher', 'months' => 6, 'industry' => 'Education'],
                'certification' => null,
                'summary' => 'English education graduate passionate about literature and language teaching.',
            ],
            [
                'first' => 'Michelle', 'last' => 'Salazar', 'gender' => 'female',
                'program' => 'Bachelor of Secondary Education Major in Filipino', 'section' => '4-4', 'batch' => 2023,
                'email' => 'alumni18@example.com',
                'skills' => ['Lesson Planning', 'Curriculum Development', 'Communication'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Filipino education graduate focused on curriculum development for secondary learners.',
            ],
            [
                'first' => 'Anton', 'last' => 'Reyes', 'gender' => 'male',
                'program' => 'Bachelor of Secondary Education Major in Science', 'section' => '4-5', 'batch' => 2024,
                'email' => 'alumni19@example.com',
                'skills' => ['Lesson Planning', 'Classroom Management', 'Critical Thinking'],
                'experience' => null,
                'certification' => null,
                'summary' => 'Science education graduate eager to make science hands-on for students.',
            ],
            [
                'first' => 'Jasmine', 'last' => 'Uy', 'gender' => 'female',
                'program' => 'Bachelor of Secondary Education Major in Social Studies', 'section' => '4-6', 'batch' => 2023,
                'email' => 'alumni20@example.com',
                'skills' => ['Lesson Planning', 'Communication', 'Critical Thinking'],
                'experience' => ['title' => 'Student Teacher', 'months' => 6, 'industry' => 'Education'],
                'certification' => null,
                'summary' => 'Social studies education graduate interested in civic education programs.',
            ],
        ];

        foreach ($alumni as $data) {
            $programId = Program::where('program_name', $data['program'])->value('program_id');
            $sectionId = Section::where('section_name', $data['section'])->value('section_id');

            $user = User::create([
                'user_email' => $data['email'],
                'user_password' => Hash::make('alumni123'),
                'user_first_name' => $data['first'],
                'user_last_name' => $data['last'],
                'user_role' => 'alumni',
                'user_active' => true,
            ]);

            $alumnus = $user->alumnus()->create([
                'program_id' => $programId,
                'section_id' => $sectionId,
                'alumnus_batch' => $data['batch'],
                'alumnus_gender' => $data['gender'],
                'alumnus_resume_summary' => $data['summary'],
                'linkedin_url' => 'https://linkedin.com/in/' . str($data['first'] . '-' . $data['last'])->slug(),
            ]);

            if (!empty($data['workplace_undisclosed'])) {
                $alumnus->update([
                    'alumnus_employment_status' => true,
                    'industry_id' => Industry::where('industry_name', 'Business & Finance')->value('industry_id'),
                    'alumnus_workplace_undisclosed' => true,
                    'alumnus_first_job_date' => Carbon::now()->subMonths(10),
                ]);
            }

            if (!empty($data['past_first_job'])) {
                $alumnus->update([
                    'alumnus_employment_status' => false,
                    'alumnus_first_job_date' => Carbon::now()->subYears(2),
                ]);
            }

            $skillIds = Skill::whereIn('skill_name', $data['skills'])->pluck('skill_id');
            $alumnus->skills()->attach($skillIds);

            if ($data['experience']) {
                $alumnus->experiences()->create([
                    'experience_type' => 'work',
                    'experience_job_title' => $data['experience']['title'],
                    'experience_job_description' => 'Contributed to daily operations and supported the team on key deliverables.',
                    'experience_duration_months' => $data['experience']['months'],
                    'industry_id' => Industry::where('industry_name', $data['experience']['industry'])->value('industry_id'),
                ]);
            }

            if ($data['certification']) {
                $alumnus->certifications()->create([
                    'certification_type' => 'certification',
                    'certification_name' => $data['certification']['name'],
                    'certification_from' => $data['certification']['from'],
                    'certification_date' => Carbon::now()->subMonths(6),
                ]);
            }

            $alumnus->refreshResumeCompleteness();

            // Every alumnus needs an ID record — without this, "Alumni ID
            // Management" only ever showed the 50 AlumniPracticeSeeder rows
            // and silently dropped these 20 named accounts (including the
            // main demo login, alumni@example.com).
            $idStatus = self::ALUMNI_ID_STATUS_POOL[array_rand(self::ALUMNI_ID_STATUS_POOL)];
            $alumnus->alumniId()->create([
                'status' => $idStatus,
                'status_updated_at' => $idStatus === 'pending' ? $alumnus->created_at : Carbon::now()->subDays(rand(1, 60)),
                'updated_by' => $idStatus !== 'pending' && $staffIds->isNotEmpty() ? $staffIds->random() : null,
            ]);
        }
    }
}
