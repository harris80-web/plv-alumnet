<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Answer to "Why is my account pending verification?". The
     * 2026_11_09_000000_update_faq_content_for_all_roles migration applies
     * the same text to databases that were seeded before it changed.
     */
    public const PENDING_VERIFICATION_ANSWER = "Alumni accounts do not require registration or verification. Your PLV-AlumNet account is created by the PLV Alumni Office, and your login credentials are sent to the email address registered with the university. If you have not received them, please check your spam or junk folder, or contact us at plvalumnet@gmail.com.\n\nEmployer accounts require approval before they can access the system. After you sign up, a PLV-AlumNet administrator will review your company details, and you will be notified by email once the review is complete. You may log in and begin using the system as soon as your account has been approved.";

    /**
     * Main-feature FAQs per role, in the order they are listed on the FAQ
     * pages. "everyone" rows are the mixed alumni and employer set shown on
     * the public FAQ page (and on both role pages). The 2026_11_09 migration
     * adds these to databases that were seeded before they existed.
     */
    public const FEATURE_FAQS = [
        // Alumni
        [
            'faq_question' => 'How do I apply for a job?',
            'faq_answer' => 'Go to the Job Board and select a job posting to view its full details, then click Apply. You can submit the resume saved on your profile, upload a different file, or use the resume you created with the Resume Builder, and you may include a cover letter if you wish. To save a posting for later, bookmark it and find it anytime under the Bookmarks tab.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'How can I track my job applications?',
            'faq_answer' => 'Open the Job Board and select the My Applications tab to see every job you have applied for along with its current status: Pending, Shortlisted, Hired, or Declined. You will receive a notification whenever an employer updates your application. If a posting reaches its closing date while your application is still pending or shortlisted, the application is closed automatically and you will be notified.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'How do I create my resume?',
            'faq_answer' => 'Open your profile and launch the Resume Builder, which guides you step by step through your summary, skills, experience, and certifications. Once completed, your resume is displayed on your profile, where you can save it as a PDF, and it can be submitted directly when you apply for jobs. You may also upload your own Resume/CV and cover letter in the Documents section of Edit Profile.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'How do I connect with fellow alumni?',
            'faq_answer' => 'Use the Directory to search for fellow PLV graduates by name, or filter the list by course and batch to find your classmates. To start a conversation, click the Messages icon at the top of the page, search for the alumnus by name, and send your message. You will be notified whenever you receive a new message.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'Can I control what other alumni see on my profile?',
            'faq_answer' => 'Yes. Go to Edit Profile and open the Profile Settings section, where you can choose whether your skills, email address, and LinkedIn profile are visible to other alumni in the Directory. You can change these settings at any time.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'How do I stay updated on campus events and announcements?',
            'faq_answer' => 'Visit the Events and Announcements pages from the main menu to see the latest news, events, and seminars from PLV. Click Interested on any event or seminar you plan to attend. You will also receive a notification whenever a new announcement, event, or seminar is posted.',
            'faq_recipient' => 'alumni',
        ],
        [
            'faq_question' => 'Can I share feedback about companies or my PLV experience?',
            'faq_answer' => 'Yes. On the Job Board, you can upvote or downvote job postings and rate a company from one to five stars, with an optional written review to help fellow alumni. You can also share a testimonial about your PLV journey from your dashboard. Testimonials are reviewed by the PLV-AlumNet team before they are published.',
            'faq_recipient' => 'alumni',
        ],

        // Employers
        [
            'faq_question' => 'How do I create a job posting?',
            'faq_answer' => 'Go to My Job Postings and click Post a New Job. Provide the job title, description, employment type, work setup, closing date, number of hires needed, and the PLV programs the role is intended for, then submit the form. A PLV-AlumNet administrator will review your posting, and you will receive a notification once it is approved or declined. Approved postings appear on the Job Board immediately.',
            'faq_recipient' => 'employer',
        ],
        [
            'faq_question' => 'How do I review and manage applicants?',
            'faq_answer' => "In My Job Postings, click Applicants on an approved job posting to see everyone who has applied. You can view each applicant's profile and resume, filter applicants by status or course, and Shortlist, Hire, or Decline them individually or in bulk. Applicants are notified automatically whenever their application status changes.",
            'faq_recipient' => 'employer',
        ],
        [
            'faq_question' => 'Can I edit a job posting after submitting it?',
            'faq_answer' => 'Yes. In My Job Postings, open the actions menu of the posting and select Edit Job Post. You can update the job details, closing date, number of hires needed, and target programs, and your changes take effect as soon as you save them.',
            'faq_recipient' => 'employer',
        ],
        [
            'faq_question' => 'How do I keep track of my job postings?',
            'faq_answer' => 'The My Job Postings page lists all of your postings with their current status: Pending Approval, Approved, Expiring Soon, or Expired. You can search your postings, narrow them down using the Filter panel, and download the list with Export CSV. Postings that close within the next seven days are marked Expiring Soon, so you can extend the closing date if you are still accepting applicants.',
            'faq_recipient' => 'employer',
        ],
        [
            'faq_question' => 'What does my employer dashboard show?',
            'faq_answer' => 'Your dashboard summarizes your recruitment activity, including active job postings, total applicants, shortlisted and hired candidates, applications awaiting review, postings that are about to expire, and monthly applications and hires. Use the Year, Job Posting, Year Graduated, College, and Course filters to focus on specific results, and click Export PDF to download a copy of the report.',
            'faq_recipient' => 'employer',
        ],
        [
            'faq_question' => 'What happens when a job posting reaches its closing date?',
            'faq_answer' => 'After its closing date, a job posting is marked as Expired and is removed from the Job Board, so it no longer receives applications. Applications that are still pending or shortlisted are closed automatically, and the applicants are notified. Hired applicants are not affected. To keep receiving applications, update the closing date before the posting expires.',
            'faq_recipient' => 'employer',
        ],

        // Everyone (alumni and employers)
        [
            'faq_question' => 'What is PLV-AlumNet?',
            'faq_answer' => 'PLV-AlumNet is the official alumni platform of Pamantasan ng Lungsod ng Valenzuela. It keeps PLV graduates connected with one another and with the university, and it links them with partner employers looking to hire PLV talent. Alumni use the platform to find job opportunities, network, and stay informed about campus news, while employers use it to post job openings and recruit qualified graduates.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'What can alumni do on PLV-AlumNet?',
            'faq_answer' => 'Alumni can browse and apply for jobs on the Job Board, bookmark postings, and track the status of their applications. They can create a resume with the Resume Builder, search the Alumni Directory and message fellow graduates, view campus announcements, events, and seminars, and monitor the claiming status of their Alumni ID and Yearbook. Alumni can also manage their profile, choose what other alumni can see, and change their password at any time.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'What can employers do on PLV-AlumNet?',
            'faq_answer' => 'Employers can post job openings for PLV graduates, review applicants, and manage the hiring process by shortlisting, hiring, or declining candidates. Their dashboard provides recruitment reports that can be filtered and exported as PDF. Employers can also view campus announcements, keep their company details and contact person up to date, and change their password at any time.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'Can I use PLV-AlumNet without an account?',
            'faq_answer' => 'Yes. Visitors can browse the Job Board, campus announcements, events and seminars, and general information about PLV-AlumNet without logging in. To apply for jobs, connect with fellow alumni, or post job openings, you need to log in with an alumni or employer account.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'How do I manage my profile?',
            'faq_answer' => 'After logging in, click the profile icon at the upper right of the page (or the menu icon on mobile) to open your account menu. Alumni can select View Profile to see their details and documents, or Edit Profile to update their employment details, contact details, documents, and privacy settings. Employers can select Company Profile to view their company page, or Edit Details to update their business information, company logo, addresses, and contact person.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'How do I change or reset my password?',
            'faq_answer' => 'To change your password, open your account menu, select Change Password, enter your old password, and then enter and confirm your new password. If you forgot your password, click Forgot Password? on the login page and enter your registered email address. We will send you a link to set a new password.',
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => 'How can I get help or contact the PLV-AlumNet team?',
            'faq_answer' => 'Logged-in alumni and employers can use the chat assistant at the lower right of the page for quick answers to common questions. When live support is available, the assistant can also connect you with a member of the PLV-AlumNet team. You may also email us at plvalumnet@gmail.com.',
            'faq_recipient' => 'everyone',
        ],
    ];

    /** The original FAQs that used to be hand-coded into the three FAQ pages. */
    private const BASE_FAQS = [
        [
            'faq_question' => 'Why is my account pending verification?',
            'faq_answer' => self::PENDING_VERIFICATION_ANSWER,
            'faq_recipient' => 'everyone',
        ],
        [
            'faq_question' => "Where can I find the University's official contact information?",
            'faq_answer' => 'Please refer to the footer section of the website for the official Facebook Page, address, and email of the university.',
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

    /**
     * Every row gets the same timestamp: the FAQ pages list the newest first
     * and break ties by id, so the order above is the order on the pages.
     * Existing questions are left alone, so running it twice adds nothing.
     */
    public function run(): void
    {
        $creator = User::whereIn('user_role', ['admin', 'super_admin'])->inRandomOrder()->first();
        $now = now();

        foreach ([...self::FEATURE_FAQS, ...self::BASE_FAQS] as $data) {
            if (Faq::where('faq_question', $data['faq_question'])->exists()) {
                continue;
            }

            $faq = new Faq($data + ['created_by' => $creator?->user_id]);
            $faq->created_at = $now;
            $faq->updated_at = $now;
            $faq->save();
        }
    }
}
