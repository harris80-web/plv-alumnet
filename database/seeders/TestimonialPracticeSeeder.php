<?php

namespace Database\Seeders;

use App\Models\Alumnus;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialPracticeSeeder extends Seeder
{
    /**
     * Sentence fragments combined at random rather than one fixed pool of
     * full sentences, so 15+ testimonials don't all read identically the
     * way a single fake()->sentence() pull would.
     */
    private const OPENERS = [
        'PLV-AlumNet made it so much easier to',
        "I wasn't expecting much, but PLV-AlumNet helped me",
        'Ever since I started using PLV-AlumNet, I was able to',
        "As a recent graduate, PLV-AlumNet let me",
        'Honestly, PLV-AlumNet has been the easiest way for me to',
    ];

    private const MIDDLES = [
        'find job openings that actually matched my course',
        'reconnect with batchmates I had lost touch with',
        'track my Alumni ID and yearbook claiming status without calling the office',
        'get notified the moment a relevant job was posted',
        'build a resume I actually felt confident sending out',
        'hear about events and seminars I would have otherwise missed',
    ];

    private const CLOSERS = [
        'It genuinely made the transition after graduation less stressful.',
        "I've already recommended it to a few batchmates.",
        'Highly recommend it to anyone graduating soon.',
        "It's become part of how I stay connected to PLV.",
        'Small thing, but it made a real difference for me.',
    ];

    /**
     * TestimonialSeeder only ever writes 4 rows, tied to 4 specific named
     * alumni. Adds a bulk pass pulled from the full alumni pool (named +
     * both practice waves) so Testimonial Management has enough rows to
     * actually need its bulk-post/bulk-hide actions. Must run after every
     * alumni seeder (AlumnusSeeder, AlumniPracticeSeeder, AlumniPracticeSeeder2).
     */
    public function run(): void
    {
        $existingAuthorIds = Testimonial::pluck('user_id')->all();
        $candidates = Alumnus::whereNotIn('user_id', $existingAuthorIds)
            ->inRandomOrder()
            ->limit(18)
            ->pluck('user_id');

        foreach ($candidates as $alumnusId) {
            $body = self::OPENERS[array_rand(self::OPENERS)] . ' '
                . self::MIDDLES[array_rand(self::MIDDLES)] . '. '
                . self::CLOSERS[array_rand(self::CLOSERS)];

            Testimonial::create([
                'testimonial_body' => $body,
                'user_id' => $alumnusId,
                // Skewed toward published so the public landing page carousel has enough to show.
                'testimonial_post' => fake()->boolean(65),
            ]);
        }
    }
}
