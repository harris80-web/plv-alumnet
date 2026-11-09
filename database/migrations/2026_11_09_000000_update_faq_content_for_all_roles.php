<?php

use Database\Seeders\FaqSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FAQ content update for databases that already have FAQs:
 *  - the "Why is my account pending verification?" answer now explains that
 *    alumni never register (their login credentials are emailed to them) and
 *    that employer accounts need admin approval;
 *  - adds the main-feature FAQs for alumni, employers and everyone
 *    (FaqSeeder::FEATURE_FAQS).
 * On a fresh install the table is still empty here, so this does nothing and
 * FaqSeeder adds the same content later. Questions already present are skipped.
 */
return new class extends Migration
{
    private const PENDING_QUESTION = 'Why is my account pending verification?';

    private const OLD_PENDING_ANSWER = "The Alumni Office needs time to manually cross-reference the data you provided (Student ID, Course, Graduation Year) with the official PLV database. This process usually takes 24-48 hours, and you'll be notified via email once approved.";

    public function up(): void
    {
        if (DB::table('faqs')->doesntExist()) {
            return;
        }

        $now = now();

        DB::table('faqs')
            ->where('faq_question', self::PENDING_QUESTION)
            ->update(['faq_answer' => FaqSeeder::PENDING_VERIFICATION_ANSWER, 'updated_at' => $now]);

        // created_by must be a staff account, so credit the new FAQs to whoever created the existing ones.
        $createdBy = DB::table('faqs')->whereNotNull('created_by')->orderBy('faq_id')->value('created_by');
        $existing = DB::table('faqs')->pluck('faq_question')->all();

        $rows = [];
        foreach (FaqSeeder::FEATURE_FAQS as $faq) {
            if (!in_array($faq['faq_question'], $existing, true)) {
                $rows[] = $faq + ['created_by' => $createdBy, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        // One insert in list order: the rows share a timestamp, and the FAQ pages break ties by id.
        if ($rows) {
            DB::table('faqs')->insert($rows);
        }
    }

    public function down(): void
    {
        DB::table('faqs')
            ->whereIn('faq_question', array_column(FaqSeeder::FEATURE_FAQS, 'faq_question'))
            ->delete();

        DB::table('faqs')
            ->where('faq_question', self::PENDING_QUESTION)
            ->where('faq_answer', FaqSeeder::PENDING_VERIFICATION_ANSWER)
            ->update(['faq_answer' => self::OLD_PENDING_ANSWER, 'updated_at' => now()]);
    }
};
