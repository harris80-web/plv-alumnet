<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\AlumniYearbook;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlumniYearbookSeeder extends Seeder
{
    private const BUILDINGS = ['Main Building', 'Engineering Building', 'Admin Building', 'Gymnasium'];
    private const ROOMS = ['Registrar\'s Office', 'Alumni Office', 'Room 101', '2nd Floor Lobby', 'Guidance Office'];

    /** Weighted so most records aren't sitting at the very first stage. */
    private const CLAIMING_STATUS_POOL = [
        'pending', 'pending', 'pending',
        'on_hand', 'on_hand', 'on_hand', 'on_hand',
        'ready_to_claim', 'ready_to_claim', 'ready_to_claim',
        'claimed', 'claimed', 'claimed', 'claimed', 'claimed',
        'not_yet_claimed', 'not_yet_claimed',
    ];

    /**
     * One alumni_yearbooks row per existing Alumnus — must run after every
     * seeder that creates alumni (AlumnusSeeder + AlumniPracticeSeeder), and
     * skips anyone who already has a row so it's safe to re-run.
     */
    public function run(): void
    {
        $staffIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');
        $existingIds = AlumniYearbook::pluck('alumnus_id')->all();

        Alumnus::whereNotIn('user_id', $existingIds)->get()->each(function (Alumnus $alumnus) use ($staffIds) {
            $claimingStatus = self::CLAIMING_STATUS_POOL[array_rand(self::CLAIMING_STATUS_POOL)];

            // Anything past "pending" implies the books physically arrived
            // on campus already — "pending" itself is a coin flip since the
            // school may have received stock before processing every record.
            $distributionStatus = $claimingStatus === 'pending'
                ? fake()->randomElement(['pending', 'on_hand'])
                : 'on_hand';

            $onHand = $distributionStatus === 'on_hand';

            AlumniYearbook::create([
                'alumnus_id' => $alumnus->user_id,
                'distribution_status' => $distributionStatus,
                'distribution_scheduled_at' => fake()->dateTimeBetween('-2 months', '+1 month'),
                'distribution_building' => $onHand ? fake()->randomElement(self::BUILDINGS) : null,
                'distribution_room_floor' => $onHand ? fake()->randomElement(self::ROOMS) : null,
                'claiming_status' => $claimingStatus,
                'status_updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'updated_by' => $staffIds->isNotEmpty() && fake()->boolean(70) ? $staffIds->random() : null,
            ]);
        });
    }
}
