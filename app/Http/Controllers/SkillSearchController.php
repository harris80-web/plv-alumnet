<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillSearchController extends Controller
{
    /**
     * GET /skills/search?q=lara
     * Returns up to 10 matching skills for the resume builder's
     * skill picker. Alumni search against the real `skills` table
     * instead of free-typing, keeping names consistent for matching.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $skills = Skill::where('skill_name', 'like', "%{$query}%")
            ->orderBy('skill_name')
            ->limit(10)
            ->get(['skill_id', 'skill_name', 'skill_category']);

        return response()->json($skills);
    }
}