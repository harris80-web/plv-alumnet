<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Every testimonial was previously hardcoded to user_id 2 (alumni@example.com)
     * regardless of how many alumni existed — spread them across a few
     * different named alumni instead so this table isn't a single-user island.
     */
    public function run(): void
    {
        $emails = ['alumni@example.com', 'alumni2@example.com', 'alumni3@example.com', 'alumni7@example.com'];
        $authors = User::whereIn('user_email', $emails)->pluck('user_id', 'user_email');

        $testimonials = [
            [
                'email' => 'alumni@example.com',
                'body' => 'PLV Alumnet has been an incredible platform for me to connect with fellow alumni and stay updated on the latest news and events. It has truly enhanced my sense of community and pride as a PLV graduate.',
                'post' => false,
            ],
            [
                'email' => 'alumni2@example.com',
                'body' => 'I have been using PLV Alumnet to find job opportunities, and it has been a game-changer for my career. The platform has made it easier for me to connect with potential employers and has opened up new doors for me in the job market.',
                'post' => false,
            ],
            [
                'email' => 'alumni3@example.com',
                'body' => 'As a recent graduate, PLV Alumnet has been a valuable resource for me to find job opportunities and connect with potential employers. The platform has made my job search much easier and more efficient.',
                'post' => true,
            ],
            [
                'email' => 'alumni7@example.com',
                'body' => 'PLV Alumnet made it so much easier to stay in touch with my batchmates and hear about opportunities I would have otherwise missed after graduating.',
                'post' => true,
            ],
        ];

        foreach ($testimonials as $data) {
            if (!isset($authors[$data['email']])) {
                continue;
            }

            Testimonial::create([
                'testimonial_body' => $data['body'],
                'user_id' => $authors[$data['email']],
                'testimonial_post' => $data['post'],
            ]);
        }
    }
}
