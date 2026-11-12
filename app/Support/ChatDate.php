<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Day-grouping labels shared by every chat surface — alumni-to-alumni
 * messages (App\Models\Message) and the ALMA/live-agent widget
 * (App\Models\ChatTicketMessage).
 *
 * Bubbles themselves show only a clock time; a dated divider is drawn
 * wherever `key()` changes between two consecutive messages, so the two
 * must be derived from the same timezone or a message could land under the
 * wrong day's divider.
 */
class ChatDate
{
    /** Every user of this platform is in the Philippines; timestamps are stored UTC. */
    public const TIMEZONE = 'Asia/Manila';

    /** Stable day identifier used only to detect a day change. */
    public static function key(CarbonInterface $local): string
    {
        return $local->format('Y-m-d');
    }

    /** Human-facing divider text for the day `$local` falls on. */
    public static function label(CarbonInterface $local): string
    {
        $today = Carbon::now(self::TIMEZONE)->startOfDay();
        $day = $local->copy()->startOfDay();

        if ($day->equalTo($today)) {
            return 'Today';
        }
        if ($day->equalTo($today->copy()->subDay())) {
            return 'Yesterday';
        }

        // Older than yesterday: drop the year once it's the current one,
        // the way messaging apps do ("March 4" vs "March 4, 2024").
        return $day->year === $today->year
            ? $day->format('F j')
            : $day->format('F j, Y');
    }
}
