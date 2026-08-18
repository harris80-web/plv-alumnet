<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployerPracticeSeeder extends Seeder
{
    public const PASSWORD = 'Employer@2026';

    private const COMPANY_WORDS = [
        'Summit', 'Horizon', 'Vertex', 'Beacon', 'Northgate', 'Ironwood', 'Bluepeak', 'Riverside',
        'Cornerstone', 'Meridian', 'Silverline', 'Anchor', 'Crestview', 'Harborlight', 'Westfield', 'Evergreen',
    ];

    private const COMPANY_SUFFIXES = ['Inc.', 'Corp.', 'Group', 'Solutions', 'Enterprises', 'Co.'];

    private const FIRST_NAMES = [
        'Antonio', 'Beatriz', 'Carmelo', 'Divina', 'Ernesto', 'Fe', 'Gilbert', 'Herminia',
        'Ignacio', 'Julieta', 'Leandro', 'Marilou', 'Norberto', 'Odessa', 'Pacifico', 'Remedios',
    ];

    private const LAST_NAMES = [
        'Alvarez', 'Bonifacio', 'Cabrera', 'Dizon', 'Espino', 'Fajardo', 'Guerrero', 'Hidalgo',
        'Isip', 'Javier', 'Katigbak', 'Lacson', 'Macaraeg', 'Nepomuceno', 'Ortiz', 'Peralta',
    ];

    /**
     * EmployerSeeder only creates one company per industry (5 total). Real
     * exercise of the job board / applicant pipeline needs more than one
     * employer per sector, and a mix of approved/pending accounts so the
     * "new employer awaiting verification" queue in User Management has
     * something to actually show instead of always being empty. Must run
     * after IndustrySeeder and before JobPostingPracticeSeeder (which posts
     * jobs under these accounts too).
     */
    public function run(): void
    {
        $industries = Industry::where('industry_name', '!=', 'None')->get();
        if ($industries->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= 12; $i++) {
            $email = "employer.practice{$i}@plvalumnet.test";
            if (User::where('user_email', $email)->exists()) {
                continue;
            }

            $company = self::COMPANY_WORDS[array_rand(self::COMPANY_WORDS)] . ' '
                . self::COMPANY_SUFFIXES[array_rand(self::COMPANY_SUFFIXES)];
            $industry = $industries->random();
            $approved = fake()->boolean(75); // ~1 in 4 sits in the verification queue

            $user = User::create([
                'user_email' => $email,
                'user_password' => Hash::make(self::PASSWORD),
                'user_first_name' => self::FIRST_NAMES[array_rand(self::FIRST_NAMES)],
                'user_last_name' => self::LAST_NAMES[array_rand(self::LAST_NAMES)],
                'user_role' => 'employer',
                // An account still awaiting verification hasn't been
                // activated either — mirrors UserController::storeEmployer().
                'user_active' => $approved,
            ]);

            $user->employer()->create([
                'employer_company_name' => $company,
                'employer_website_url' => 'https://www.' . Str::slug($company) . '.com',
                // Column is an unsignedBigInteger (plain headcount), not the
                // string range the profile-edit form's validation implies.
                'employer_company_size' => fake()->numberBetween(5, 800),
                'employer_year_established' => fake()->numberBetween(1998, 2022),
                'industry_id' => $industry->industry_id,
                'employer_approved' => $approved,
                'employer_company_document' => 'companyDocuments/' . Str::slug($company) . '.pdf',
            ]);
        }
    }
}
