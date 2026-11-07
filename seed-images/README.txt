PUT YOUR OWN IMAGES HERE (for dummy data)

  seed-images\jobs\       job posting images   (one per job; any name, .jpg/.png/.webp)
  seed-images\notices\    announcement, event and seminar images
  seed-images\profiles\   alumni / employer profile photos
  seed-images\logos\      company logos

Then run:
  php artisan images:import-seed --assign-jobs

- Files are copied into storage/app/public (the app's upload folders). Your originals stay here.
- --assign-jobs gives each example job posting an image of its course, using the
  course images in seed-images\jobs\course-<program id>.png (generated for all 17 courses).
  Jobs that already have a working image keep it.
