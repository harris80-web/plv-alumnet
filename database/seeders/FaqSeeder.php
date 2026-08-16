<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Carries over the content that used to be hand-coded into the three
     * public FAQ pages (alumni/employer/general), now recipient-tagged so
     * each page can query real rows instead of duplicating hardcoded HTML.
     */
    public function run(): void
    {
        $creator = User::whereIn('user_role', ['admin', 'super_admin'])->inRandomOrder()->first();

        $faqs = [
            [
                'faq_question' => 'Why is my account pending verification?',
                'faq_answer' => "The Alumni Office needs time to manually cross-reference the data you provided (Student ID, Course, Graduation Year) with the official PLV database. This process usually takes 24-48 hours, and you'll be notified via email once approved.",
                'faq_recipient' => 'everyone',
            ],
            [
                'faq_question' => "Where can I find the University's official contact information?",
                'faq_answer' => "Please refer to the footer section of the website for the official Facebook Page, address, and email of the university.",
                'faq_recipient' => 'everyone',
            ],
            [
                'faq_question' => 'How do I sign up as an alumni?',
                'faq_answer' => "You don't need to manually sign up as an alumni. Once you graduate, you will be automatically registered in the system. An email will be sent to you containing your login credentials. For now, only employers are allowed to create accounts. Welcome to PLV-AlumNet!",
                'faq_recipient' => 'alumni',
            ],
            [
                'faq_question' => 'How can I track my Alumni ID or Yearbook claiming status?',
                'faq_answer' => 'Navigate to the ID/Yearbook Tracker section on your dashboard. Follow the instructions to monitor its claiming status.',
                'faq_recipient' => 'alumni',
            ],
            [
                'faq_question' => 'I am an employer. How do I post a job?',
                'faq_answer' => 'Click the "Sign Up" button and register your company profile. Once your company is approved by our admins, you will gain access to the Job Board where you can post and manage job postings and applicants.',
                'faq_recipient' => 'employer',
            ],
            [
                'faq_question' => 'How long does it take for a job posting to go live?',
                'faq_answer' => "Job postings typically go live immediately after submission once approved by the system's administrator. However, PLV reserves the right to review new employer accounts or flag unusual postings, which may delay publication by a few days.",
                'faq_recipient' => 'employer',
            ],
        ];

        foreach ($faqs as $data) {
            Faq::create($data + ['created_by' => $creator?->user_id]);
        }
    }
}
