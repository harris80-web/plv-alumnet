<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Testimonial::create([
            'testimonial_body' => 'PLV Alumnet has been an incredible platform for me to connect with fellow alumni and stay updated on the latest news and events. It has truly enhanced my sense of community and pride as a PLV graduate.',
            'user_id' => 2,
            'testimonial_post' => false,
        ]);

        Testimonial::create([
            'testimonial_body' => 'I have been using PLV Alumnet to find job opportunities, and it has been a game-changer for my career. The platform has made it easier for me to connect with potential employers and has opened up new doors for me in the job market.',
            'user_id' => 2,
            'testimonial_post' => false,
        ]);

        Testimonial::create([
            'testimonial_body' => 'As a recent graduate, PLV Alumnet has been a valuable resource for me to find job opportunities and connect with potential employers. The platform has made my job search much easier and more efficient.',
            'user_id' => 2,
            'testimonial_post' => true,
        ]);

        Testimonial::create([
            'testimonial_body' => 'My employer has been using PLV Alumnet to find talented graduates for our company, and it has been a game-changer. The platform has made it easier for us to connect with potential candidates and has streamlined our recruitment process.',
            'user_id' => 2,
            'testimonial_post' => true,
        ]);
    }
}
