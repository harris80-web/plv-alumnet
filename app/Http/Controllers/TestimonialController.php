<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
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
    public function show(Testimonial $testimonial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        //
    }

    public function submitTestimonial(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'testimonial_body' => 'required|string|max:1000',
        ]);

        // Create a new testimonial record in the database
        try {
            DB::transaction(function () use ($validatedData, $id) {
                Testimonial::create([
                    'testimonial_body' => $validatedData['testimonial_body'],
                    'user_id' => $id,
                    'testimonial_post' => false,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add admin: ' . $e->getMessage()]);
        }
        $testimonials = Testimonial::all();


        // Redirect back with a success message
        return redirect()->route('users.dashboardRedirect', compact('testimonials'))->with('success', 'Your testimonial has been submitted successfully!');
    }

    public function showTestimonials()
    {
        $testimonials = Testimonial::with(['alumnus.user', 'alumnus.program'])->get();
        return view('superAdmin.testimonialManagement', compact('testimonials'));
    }

    public function postTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $testimonial->testimonial_post = true;
        $testimonial->save();

        return back()->with('success', 'Status updated successfully!');
    }

    public function bulkPost(Request $request)
    {
        $ids = explode(',', $request->input('ids'));
        Testimonial::whereIn('testimonial_id', $ids)->update(['testimonial_post' => true]);
        return back()->with('success', 'Selected testimonials published successfully!');
    }
}
