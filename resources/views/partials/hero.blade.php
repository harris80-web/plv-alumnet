{{--
    Shared hero banner — the blue/orange strip under the header on the general,
    alumni and employer pages. Every page uses this one copy, so the title and
    subtitle sit in exactly the same place everywhere.

    The left indent follows the screen size (responsive-public.css,
    --rp-hero-indent): the page's side padding on phones/tablets, 40px on
    laptops, 48px from 1280px, 64px from 1536px. Title/subtitle sizes scale
    the same way (--rp-hero-title / --rp-hero-sub).

    The page still sets the .HeroSection background image in its own <style>.

    Params:
      $title     — the page title ("Welcome to PLV-AlumNet!", "About", …)
      $subtitle  — optional; defaults to the PLV-AlumNet tagline
--}}
<section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
    <div class="rp-hero-text w-full my-7">
        <h1 class="text-5xl font-bold mb-1.5">{{ $title }}</h1>
        <p class="text-xl font-light">{{ $subtitle ?? 'PLV-AlumNet: Honoring the Past. Shaping the Future.' }}</p>
    </div>
</section>
