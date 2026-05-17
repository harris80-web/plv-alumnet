<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeclineOnJobExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:decline-on-job-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically decline shortlisted applicants on expired jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        // 1. Find all job IDs that expired before today
        $expiredJobIds = JobPosting::where('job_closing_date', '<', Carbon::today())
                                    ->pluck('id');

        // 2. Mass-update all 'shortlisted' applicants for those specific jobs to 'declined'
        $updatedRows = JobApplication::whereIn('job_posting_id', $expiredJobIds)
            ->where('application_status', 'shortlisted')
            ->update(['application_status' => 'declined']);

        $this->info("Successfully declined {$updatedRows} shortlisted applicants from expired jobs.");
    }
}
