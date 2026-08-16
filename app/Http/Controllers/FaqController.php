<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    private function validationRules(): array
    {
        return [
            'faq_question' => ['required', 'string', 'max:1000'],
            'faq_answer' => ['required', 'string', 'max:5000'],
            'faq_recipient' => ['required', 'in:' . implode(',', Faq::RECIPIENTS)],
        ];
    }

    /** Admin/super-admin FAQ management table. */
    public function index()
    {
        $this->authorizeStaff();

        $faqs = Faq::with('creator')->orderByDesc('created_at')->orderByDesc('faq_id')->get();

        $counts = [
            'total' => $faqs->count(),
            'everyone' => $faqs->where('faq_recipient', 'everyone')->count(),
            'alumni' => $faqs->where('faq_recipient', 'alumni')->count(),
            'employer' => $faqs->where('faq_recipient', 'employer')->count(),
        ];

        return view('superAdmin.faqManagement', compact('faqs', 'counts'));
    }

    public function store(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate($this->validationRules());

        Faq::create($validated + ['created_by' => Auth::id()]);

        return back()->with('success', 'FAQ added successfully.');
    }

    public function update(Request $request, Faq $faq)
    {
        $this->authorizeStaff();

        $validated = $request->validate($this->validationRules());

        $faq->update($validated);

        return back()->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $this->authorizeStaff();

        $faq->delete();

        return back()->with('success', 'FAQ deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,faq_id'],
        ]);

        $count = Faq::whereIn('faq_id', $validated['ids'])->delete();

        return back()->with('success', "{$count} FAQ(s) deleted.");
    }

    public function bulkUpdateRecipient(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,faq_id'],
            'faq_recipient' => ['required', 'in:' . implode(',', Faq::RECIPIENTS)],
        ]);

        $count = Faq::whereIn('faq_id', $validated['ids'])->update([
            'faq_recipient' => $validated['faq_recipient'],
        ]);

        $label = Faq::recipientLabels()[$validated['faq_recipient']];

        return back()->with('success', "{$count} FAQ(s) updated to {$label}.");
    }

    /** Public, not-logged-in FAQ page — only FAQs meant for everyone. */
    public function generalFaqs()
    {
        $faqs = Faq::visibleToGeneral()->orderByDesc('created_at')->get();

        return view('general.faqs', compact('faqs'));
    }

    public function alumniFaqs()
    {
        $faqs = Faq::visibleToAlumni()->orderByDesc('created_at')->get();

        return view('alumni.faqs', compact('faqs'));
    }

    public function employerFaqs()
    {
        $faqs = Faq::visibleToEmployer()->orderByDesc('created_at')->get();

        return view('employer.faqs', compact('faqs'));
    }
}
