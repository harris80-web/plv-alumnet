<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Industry;
use App\Models\Skill;
use App\Models\User;
use App\Services\ResumeTextParser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfTextParser;

class ResumeBuilderController extends Controller
{
    /**
     * Reads text out of an uploaded PDF and runs it through ResumeTextParser.
     * Returns the extracted fields as JSON — nothing is saved here. The
     * wizard prefills itself from the response so the alumnus reviews and
     * edits before anything actually hits the database, same as any other
     * prefill (see toResumeFormArray()).
     */
    public function import(Request $request)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $text = (new PdfTextParser())->parseFile($request->file('resume_file')->getRealPath())->getText();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not read that PDF. Make sure it\'s not scanned/image-only or password-protected.',
            ], 422);
        }

        if (trim($text) === '') {
            return response()->json([
                'message' => 'That PDF has no readable text (likely a scanned image). Try a different file.',
            ], 422);
        }

        return response()->json((new ResumeTextParser())->parse($text));
    }

    /**
     * Real, text-based PDF (not a screenshot) — renders the same data as
     * the on-screen resume through a plain-CSS template, since dompdf
     * doesn't run the Tailwind CDN's browser-side JIT compiler.
     */
    public function downloadPdf()
    {
        $user = User::with(['alumnus.program', 'alumnus.skills', 'alumnus.experiences.industry', 'alumnus.certifications'])
            ->findOrFail(Auth::id());

        $pdf = Pdf::loadView('alumni.resume-pdf', compact('user'));

        $filename = Str::slug($user->alumnus->resumeFullName() ?: 'resume') . '-resume.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show the resume builder, prefilled with whatever the alumnus has
     * already saved.
     */
    public function edit()
    {
        $alumnus = Alumnus::with(['skills', 'experiences', 'certifications'])
            ->findOrFail(Auth::id());

        $resumeData = $alumnus->toResumeFormArray();

        $industries = Industry::orderBy('industry_name')->get(['industry_id', 'industry_name']);

        return view('resume.builder', compact('resumeData', 'industries'));
    }

    /**
     * Persist the whole resume in one go. Skills/experiences/certifications
     * are replaced wholesale each save — simplest correct behavior for a
     * wizard where rows can be freely added/removed/reordered client-side.
     */
    public function save(Request $request)
    {
        // Let Alumnus type "linkedin.com/in/juandelacruz" without a scheme —
        // Laravel's `url` rule requires http(s):// to pass, so normalize
        // here before validating.
        $linkedin = $request->input('linkedin_url');
        if ($linkedin && ! preg_match('~^https?://~i', $linkedin)) {
            $request->merge(['linkedin_url' => 'https://' . $linkedin]);
        }

        $validated = $request->validate([
            'resume_summary' => ['nullable', 'string', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],

            'skills' => ['array'],
            'skills.*.name' => ['required_with:skills', 'string', 'max:100'],

            'experiences' => ['array'],
            'experiences.*.type' => ['required_with:experiences', 'in:work,project'],
            'experiences.*.job_title' => ['required_with:experiences', 'string', 'max:150'],
            'experiences.*.job_description' => ['nullable', 'string', 'max:2000'],
            'experiences.*.duration_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'experiences.*.industry_id' => ['nullable', 'exists:industries,industry_id'],

            'certifications' => ['array'],
            'certifications.*.certification_type' => ['required_with:certifications', 'in:certification,seminar,training'],
            'certifications.*.certification_name' => ['required_with:certifications', 'string', 'max:150'],
            'certifications.*.certification_from' => ['nullable', 'string', 'max:150'],
            'certifications.*.certification_date' => ['nullable', 'date'],
        ]);

        $alumnus = Alumnus::findOrFail(Auth::id());

        DB::transaction(function () use ($alumnus, $validated) {
            // ---- Summary & links ----
            $alumnus->alumnus_resume_summary = $validated['resume_summary'] ?? null;
            $alumnus->linkedin_url = $validated['linkedin_url'] ?? null;
            $alumnus->save();

            // ---- Skills: find-or-create by name, then sync the pivot ----
            $skillIds = collect($validated['skills'] ?? [])
                ->pluck('name')
                ->filter()
                ->map(fn ($name) => trim($name))
                ->unique()
                ->map(function ($name) {
                    $skill = Skill::firstOrCreate(
                        ['skill_name' => $name],
                        ['skill_category' => 'domain'] // safe default; admin can recategorize later
                    );
                    return $skill->skill_id;
                });

            $alumnus->skills()->sync($skillIds);

            // ---- Experience & projects: replace wholesale ----
            $alumnus->experiences()->delete();
            foreach ($validated['experiences'] ?? [] as $exp) {
                Experience::create([
                    'alumnus_id' => $alumnus->user_id,
                    'experience_type' => $exp['type'],
                    'experience_job_title' => $exp['job_title'],
                    'experience_job_description' => $exp['job_description'] ?? null,
                    // Use ?: (not ??) here: these fields are always present
                    // in the submitted array, just possibly empty strings
                    // when left blank in the form. ?? only catches a missing
                    // key, not an empty value. `experience_duration_months`
                    // is NOT NULL (default 0), so an explicit '' -> null
                    // still violates the column — fall back to 0 instead.
                    // `industry_id` is a nullable FK, so null is the correct
                    // "not specified" value there.
                    'experience_duration_months' => $exp['duration_months'] ?: 0,
                    'industry_id' => $exp['industry_id'] ?: null,
                ]);
            }

            // ---- Certifications & seminars: replace wholesale ----
            $alumnus->certifications()->delete();
            foreach ($validated['certifications'] ?? [] as $cert) {
                Certification::create([
                    'alumnus_id' => $alumnus->user_id,
                    'certification_type' => $cert['certification_type'],
                    'certification_name' => $cert['certification_name'],
                    'certification_from' => $cert['certification_from'] ?: null,
                    'certification_date' => $cert['certification_date'] ?: null,
                ]);
            }

            // ---- Recompute completeness (source of truth, server-side) ----
            // Alumnus::refreshResumeCompleteness() re-queries skills/experiences/
            // certifications itself, so no need to refresh/reload first.
            $alumnus->refreshResumeCompleteness();
        });

        $alumnus = $alumnus->fresh(['skills', 'experiences', 'certifications']);

        return response()->json([
            'resume_completeness' => $alumnus->alumnus_resume_completeness,
            'breakdown' => $alumnus->completenessBreakdown(),
        ]);
    }
}