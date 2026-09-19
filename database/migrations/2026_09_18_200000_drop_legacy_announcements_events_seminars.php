<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * announcements/events/seminars predate the unified `notices` table
 * (category enum: event|seminar|announcement) and were superseded by it —
 * every real route (NoticeController's guest/alumni/employer announcement
 * pages, the homepage preview) already reads from `notices`, filtered by
 * category. Confirmed dead before dropping: AnnouncementController was
 * never registered in routes/web.php at all; EventController/
 * SeminarController *were* registered via Route::resource(), which is why
 * the first cleanup pass missed them, but every method body was empty
 * (`{ // }`, unimplemented make:controller --resource scaffolding) —
 * nothing anywhere links to those routes, no seeder writes to any of the
 * three tables, and all three hold 0 rows live.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('events');
        Schema::dropIfExists('seminars');
    }

    public function down(): void
    {
        // Not reconstructed — all three held 0 rows at drop time and
        // nothing in the app references them anymore.
    }
};
