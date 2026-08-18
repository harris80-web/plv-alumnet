<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\Notice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NoticePracticeSeeder extends Seeder
{
    private const EVENT_TITLES = [
        'Batch 2018 Reunion Night', 'PLV Sportsfest Alumni Cup', 'Alumni-Student Mentorship Mixer',
        'Founders Day Alumni Parade', 'Regional Alumni Chapter Meetup', 'Alumni Giving Day Kickoff',
    ];

    private const SEMINAR_TITLES = [
        'Resume Building Workshop', 'Interview Skills Bootcamp', 'Personal Branding for Job Seekers',
        'Freelancing 101 for Graduates', 'Workplace Mental Health Awareness', 'Public Speaking for Professionals',
        'Introduction to Investing', 'Remote Work Productivity Seminar',
    ];

    private const ANNOUNCEMENT_TITLES = [
        'Updated Alumni ID Claiming Hours', 'New Job Board Features Released', 'Office Holiday Schedule',
        'Alumni Directory Privacy Reminder', 'Scholarship Application Window Now Open', 'Portal Password Policy Update',
    ];

    private const LOCATIONS = ['PLV Gymnasium', 'PLV Auditorium', 'Business Center Hall', 'Room 204, Main Building', 'PLV Grandstand', 'Engineering Building Lobby'];

    private const SPEAKERS = [
        ['name' => 'Ms. Teresa Ongpin', 'topic' => 'Standing Out in a Competitive Job Market'],
        ['name' => 'Mr. Danilo Cruz', 'topic' => 'Building a Personal Brand Online'],
        ['name' => 'Dr. Marissa Loyola', 'topic' => 'Managing Stress in Your First Job'],
        ['name' => 'Mr. Enrico Tan', 'topic' => 'Getting Started with Freelance Work'],
    ];

    /**
     * NoticeSeeder's 8 hand-written rows are enough to demonstrate every
     * category/recipient combination, but not enough to exercise pagination
     * (6 per page — see NoticeController::alumniAnnouncements/
     * alumniEventsAndSeminars). Adds a bulk pass spanning past and future
     * dates. Must run after AlumnusSeeder + AlumniPracticeSeeder(2) so
     * there's a real alumni pool to attach as "interested".
     */
    public function run(): void
    {
        $creators = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');
        $alumniPool = Alumnus::pluck('user_id');

        if ($creators->isEmpty()) {
            return;
        }

        $existingTitles = Notice::pluck('title')->all();
        $pools = [
            'event' => self::EVENT_TITLES,
            'seminar' => self::SEMINAR_TITLES,
            'announcement' => self::ANNOUNCEMENT_TITLES,
        ];

        for ($i = 0; $i < 18; $i++) {
            $category = fake()->randomElement(['event', 'seminar', 'announcement']);
            $titlePool = array_diff($pools[$category], $existingTitles);
            if (empty($titlePool)) {
                continue;
            }
            $title = $titlePool[array_rand($titlePool)];
            $existingTitles[] = $title;

            // Skewed toward the future so the events/seminars pages (which
            // default-sort ascending by date) mostly show upcoming ones.
            $eventDatetime = fake()->boolean(70)
                ? Carbon::now()->addDays(fake()->numberBetween(1, 120))->setTime(fake()->numberBetween(8, 16), fake()->randomElement([0, 30]))
                : Carbon::now()->subDays(fake()->numberBetween(1, 90))->setTime(fake()->numberBetween(8, 16), 0);

            $speaker = self::SPEAKERS[array_rand(self::SPEAKERS)];

            $notice = Notice::create([
                'category' => $category,
                'title' => $title,
                'event_datetime' => $eventDatetime,
                'location' => $category !== 'announcement' ? self::LOCATIONS[array_rand(self::LOCATIONS)] : null,
                'description' => fake()->paragraph(3),
                'recipient' => fake()->randomElement(['alumni', 'alumni', 'general', 'everyone']),
                'speaker_name' => $category === 'seminar' ? $speaker['name'] : null,
                'speaker_topic' => $category === 'seminar' ? $speaker['topic'] : null,
                'created_by' => $creators->random(),
            ]);

            if ($category !== 'announcement' && $alumniPool->isNotEmpty() && fake()->boolean(70)) {
                $interestCount = fake()->numberBetween(1, min(20, $alumniPool->count()));
                $notice->interestedAlumni()->attach($alumniPool->random($interestCount));
            }
        }
    }
}
