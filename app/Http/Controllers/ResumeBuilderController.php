<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Industry;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResumeBuilderController extends Controller
{
    /**
     * Show the resume builder, prefilled with whatever the alumnus has
     * already saved.
     */
    public function edit()
    {
        $alumnus = Alumnus::with(['skills', 'experiences', 'certifications'])
            ->findOrFail(Auth::id());

        $resumeData = [
            'resume_summary' => $alumnus->alumnus_resume_summary,
            'linkedin_url' => $alumnus->linkedin_url,
            'resume_completeness' => $alumnus->alumnus_resume_completeness ?? 0,
            'skills' => $alumnus->skills->map(fn ($skill) => [
                'name' => $skill->skill_name,
            ])->values(),
            'experiences' => $alumnus->experiences->map(fn ($exp) => [
                'type' => $exp->experience_type,
                'job_title' => $exp->experience_job_title,
                'job_description' => $exp->experience_job_description,
                'duration_months' => $exp->experience_duration_months,
                'industry_id' => $exp->industry_id,
            ])->values(),
            'certifications' => $alumnus->certifications->map(fn ($cert) => [
                'certification_type' => $cert->certification_type,
                'certification_name' => $cert->certification_name,
                'certification_from' => $cert->certification_from,
                'certification_date' => optional($cert->certification_date)->format('Y-m-d'),
            ])->values(),
        ];

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
                        ['category' => 'domain'] // safe default; admin can recategorize later
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
                    // when left blank in the form. ?? only catches a
                    // missing key, not an empty value — MySQL rejects ''
                    // for integer columns with no default, which is what
                    // throws the "doesn't have a default value" error.
                    'experience_duration_months' => $exp['duration_months'] ?: null,
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
            $alumnus->refresh()->load(['skills', 'experiences', 'certifications']);
            $alumnus->alumnus_resume_completeness = $this->calculateCompleteness($alumnus);
            $alumnus->save();
        });

        return response()->json([
            'resume_completeness' => $alumnus->fresh()->alumnus_resume_completeness,
        ]);
    }

    private function calculateCompleteness(Alumnus $alumnus): int
    {
        $score = 0;

        if (Str::length((string) $alumnus->alumnus_resume_summary) > 40) {
            $score += 15;
        }
        if (! empty($alumnus->linkedin_url)) {
            $score += 10;
        }

        // Full credit for the section as soon as there's at least one entry
        // — no partial credit scaled by how many they added.
        if ($alumnus->skills->isNotEmpty()) {
            $score += 25;
        }
        if ($alumnus->experiences->isNotEmpty()) {
            $score += 30;
        }
        if ($alumnus->certifications->isNotEmpty()) {
            $score += 20;
        }

        return min(100, (int) round($score));
    }
}