{{--
    Shared "Alumni Testimonials" cards + pagination — included by both
    general/home.blade.php and alumni/dashboard.blade.php for the initial
    page load, AND returned as-is by TestimonialController::cardsFragment()
    for the AJAX page-change requests those two pages' JS makes (see
    partials/testimonial-cards-script.blade.php). Keeping one partial for
    both means the fetched fragment can never drift from the server-rendered
    version.
--}}
<div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8">
    @forelse ($testimonials as $testimonial)
    <div class="bg-white text-left p-6 rounded-lg shadow-2xl relative flex gap-4">
        <div class="flex-shrink-0">
            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center">
                @if ($testimonial->alumnus->user->user_profile_picture)
                <img src="{{ asset('storage/' . $testimonial->alumnus->user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover rounded-full">
                @else
                <i class="fa-solid fa-user text-3xl text-white"></i>
                @endif
            </div>
        </div>
        <div>
            <h4 class="text-blue-900 font-bold uppercase text-lg">{{ $testimonial->alumnus->user->user_first_name }} {{ $testimonial->alumnus->user->user_last_name }}</h4>
            <p class="text-blue-700 text-[10px] font-semibold mb-3 uppercase">{{ $testimonial->alumnus->program->program_name }}, Batch {{ $testimonial->alumnus->alumnus_batch }}</p>
            <p class="text-gray-600 text-xs leading-relaxed">{{ $testimonial->testimonial_body }}</p>
        </div>
    </div>
    @empty
    <p class="text-white/70 col-span-2">No testimonials to show yet.</p>
    @endforelse
</div>

{{ $testimonials->onEachSide(1)->links('partials.pagination-light') }}
