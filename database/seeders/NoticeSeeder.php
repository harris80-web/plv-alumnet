<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\Notice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Must run after AlumnusSeeder + CohortSeeder — attaches a
     * random handful of existing alumni as "interested" on the event/seminar
     * rows so the Attendance column has real, non-zero counts to show
     * (announcements are left at 0 interest — nothing to "attend").
     */
    public function run(): void
    {
        $creator = User::whereIn('user_role', ['admin', 'super_admin'])->inRandomOrder()->first();
        $alumniPool = Alumnus::pluck('user_id');

        $notices = [
            [
                'category' => 'event',
                'title' => 'Alumni Homecoming 2026',
                'event_datetime' => Carbon::now()->addDays(30)->setTime(9, 0),
                'location' => 'PLV Gymnasium',
                'description' => 'Join us for a day of reunions, campus tours, and games as we welcome alumni of every batch back to PLV.',
                'recipient' => 'alumni',
                'interest' => 12,
            ],
            [
                'category' => 'seminar',
                'title' => 'Career Growth After Graduation',
                'thumbnail' => 'jobImages/softdev.png',
                'event_datetime' => Carbon::now()->addDays(14)->setTime(13, 30),
                'location' => 'PLV Auditorium',
                'description' => 'A guided discussion on navigating promotions, career pivots, and skill-building in your first five years post-graduation.',
                'recipient' => 'alumni',
                'speaker_name' => 'Dr. Ana Reyes',
                'speaker_topic' => 'Navigating Your First 5 Years in the Workforce',
                'interest' => 9,
            ],
            [
                'category' => 'announcement',
                'title' => 'Yearbook Distribution Schedule Released',
                'event_datetime' => Carbon::now()->subDays(3)->setTime(8, 0),
                'description' => 'Batch-by-batch pickup schedules are now posted. Check the Alumni Yearbook page for your claiming status and pickup window.',
                'recipient' => 'alumni',
                'interest' => 0,
            ],
            [
                'category' => 'event',
                'title' => 'PLV-AlumNet Job Fair 2026',
                'thumbnail' => 'jobImages/networkenginerr.png',
                'event_datetime' => Carbon::now()->addDays(45)->setTime(9, 0),
                'location' => 'PLV Gymnasium',
                'description' => 'Meet employers actively hiring PLV graduates across IT, education, business, and engineering roles. Bring your resume.',
                'recipient' => 'everyone',
                'interest' => 18,
            ],
            [
                'category' => 'seminar',
                'title' => 'Financial Literacy 101',
                'thumbnail' => 'jobImages/accounting.jpg',
                'event_datetime' => Carbon::now()->addDays(21)->setTime(14, 0),
                'location' => 'Room 204, Main Building',
                'description' => 'Practical budgeting, saving, and investing basics for young professionals just starting their careers.',
                'recipient' => 'general',
                'speaker_name' => 'Mr. Carlos Bautista',
                'speaker_topic' => 'Budgeting and Saving for Young Professionals',
                'interest' => 6,
            ],
            [
                'category' => 'announcement',
                'title' => 'PLV-AlumNet System Maintenance Notice',
                'event_datetime' => Carbon::now()->addDays(2)->setTime(22, 0),
                'description' => 'The platform will be briefly unavailable for scheduled maintenance. We expect no more than an hour of downtime.',
                'recipient' => 'everyone',
                'interest' => 0,
            ],
            [
                'category' => 'event',
                'title' => 'PLV Founding Anniversary Celebration',
                'event_datetime' => Carbon::now()->subDays(15)->setTime(8, 0),
                'location' => 'PLV Grandstand',
                'description' => 'A campus-wide celebration marking another year of PLV, featuring alumni speakers and student performances.',
                'recipient' => 'everyone',
                'interest' => 15,
            ],
            [
                'category' => 'seminar',
                'title' => 'Entrepreneurship Bootcamp',
                'thumbnail' => 'jobImages/q1y0JaVz4IA7oWkm3LLuU0fpBABRjt2Duyz7nYhI.png',
                'event_datetime' => Carbon::now()->addDays(38)->setTime(9, 30),
                'location' => 'Business Center Hall',
                'description' => 'From idea validation to your first sale — a hands-on workshop for alumni exploring starting their own business.',
                'recipient' => 'alumni',
                'speaker_name' => 'Ms. Liza Fernandez',
                'speaker_topic' => 'From Idea to Startup',
                'interest' => 7,
            ],
        ];

        foreach ($notices as $data) {
            $interest = $data['interest'];
            unset($data['interest']);

            $notice = Notice::create($data + ['created_by' => $creator?->user_id]);

            if ($interest > 0 && $alumniPool->isNotEmpty()) {
                $notice->interestedAlumni()->attach(
                    $alumniPool->random(min($interest, $alumniPool->count()))
                );
            }
        }
    }
}
