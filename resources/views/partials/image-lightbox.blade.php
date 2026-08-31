{{--
    Shared full-size image viewer, used by every "See/View Profile Image"
    button and profile-picture/logo preview across the app (superAdmin,
    alumni, and employer profile pages). Include once per page via
    @include('partials.image-lightbox'), then call openImageLightbox(src)
    from any button/link.
--}}
<div id="imageLightbox" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 p-6"
    onclick="closeImageLightbox()">
    <button type="button" onclick="closeImageLightbox()"
        class="absolute top-5 right-6 text-white/80 hover:text-white text-3xl leading-none">&times;</button>
    <img id="imageLightboxImg" src="" alt="Full size image"
        class="max-w-full max-h-full rounded-lg shadow-2xl" onclick="event.stopPropagation()">
</div>
<script>
    /**
     * Opens the shared lightbox with the given image URL. No-ops on a
     * falsy/empty src so a "View Image" action on a profile/logo that has
     * nothing uploaded yet does nothing instead of showing a broken image.
     */
    function openImageLightbox(src) {
        if (!src) return;
        document.getElementById('imageLightboxImg').src = src;
        const modal = document.getElementById('imageLightbox');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeImageLightbox() {
        const modal = document.getElementById('imageLightbox');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    /**
     * Shared "pick a file -> live-preview it" handler for a profile
     * picture/logo <img>. Swaps a same-container placeholder icon (if any)
     * out of view once a real image is showing. Silently ignores non-image
     * files/empty selections rather than throwing.
     *
     * Sets display via inline style, not just the `hidden` class — a page
     * that double-loads a CSS library (this app's alumni/employer footers
     * used to load a second copy of FontAwesome) can end up with a rule of
     * equal specificity to Tailwind's `.hidden{display:none}` winning the
     * cascade tie, silently un-hiding the element the class was supposed to
     * hide. An inline style always wins regardless of any such conflict.
     */
    function previewImageInput(input, imgId, placeholderId) {
        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const img = document.getElementById(imgId);
        if (!img) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.classList.remove('hidden');
            img.style.display = '';
            if (placeholderId) {
                const placeholder = document.getElementById(placeholderId);
                if (placeholder) {
                    placeholder.classList.add('hidden');
                    placeholder.style.display = 'none';
                }
            }
        };
        reader.readAsDataURL(file);
    }
</script>
