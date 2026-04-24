<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobPostingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosting $jobPosting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosting $jobPosting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosting $jobPosting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosting $jobPosting)
    {
        //
    }

    public function showJobBoard()
    {
        $jobPostings = JobPosting::all();
        $programs = Program::all();
        $users = Auth::user();
        return view('general.jobBoard', compact('jobPostings', 'programs', 'users'));
    }

    public function addJobPost(Request $request, $id)
    {
       $validated = $request->validate([
            'job_posting_image' => 'required|image|mimes:jpeg,png,jpg,svg',
            'job_posting_title' => 'required|string',
            'job_posting_company' => 'required|string',
            'job_posting_address' => 'required|string',
            'job_posting_employment_type' => 'required|string',
            'job_posting_description' => 'required|string',
            'job_closing_date' => 'required|date',
            'job_posting_setup' => 'required|string',
            'program' => 'required|exists:programs,program_id',
        ]);

        
        $jobImagePath = null;
        if ($request->hasFile('job_posting_image')) {
            $jobImagePath = $request->file('job_posting_image')->store('jobImages', 'public');
        }

        try{
            DB::transaction(function() use($validated, $jobImagePath, $id) {
                $jobPost = JobPosting::create([
                    'job_posting_image' => $jobImagePath,
                    'job_posting_title' => $validated['job_posting_title'],
                    'job_posting_company' => $validated['job_posting_company'],
                    'job_posting_address' => $validated['job_posting_address'],
                    'job_posting_employment_type' => $validated['job_posting_employment_type'],
                    'job_posting_description' => $validated['job_posting_description'],
                    'job_closing_date' => $validated['job_closing_date'],
                    'job_posting_setup' => $validated['job_posting_setup'],
                    'program_id' => $validated['program'],
                    'employer_id' => $id,
                ]);
        });
        } catch (\Exception $e) {
            if ($jobImagePath) {
                // Delete the uploaded image if the transaction fails
                Storage::disk('public')->delete($jobImagePath);
            }
            return back()->withErrors(['error' => 'Failed to upload job posting image. Please try again.']);
        }

        return redirect()->route('jobPosting.jobBoard')->with('success', 'Job posting added successfully!');

       
    }
}
