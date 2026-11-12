<?php

namespace App\Support;

/**
 * Compact human-readable durations for the admin dashboards, which measure
 * everything in whole minutes. A raw minute count stops being readable past
 * an hour or so ("2315m"), so anything that long is rolled up into days and
 * hours — at most two units, since these are at-a-glance stat tiles.
 */
class Duration
{
    private const MINUTES_PER_HOUR = 60;
    private const MINUTES_PER_DAY = 1440;

    public static function fromMinutes(?int $minutes): string
    {
        $minutes = max(0, (int) $minutes);

        if ($minutes < self::MINUTES_PER_HOUR) {
            return $minutes . 'm';
        }

        if ($minutes < self::MINUTES_PER_DAY) {
            $hours = intdiv($minutes, self::MINUTES_PER_HOUR);
            $remainder = $minutes % self::MINUTES_PER_HOUR;

            return $remainder === 0 ? $hours . 'h' : $hours . 'h ' . $remainder . 'm';
        }

        $days = intdiv($minutes, self::MINUTES_PER_DAY);
        $hours = intdiv($minutes % self::MINUTES_PER_DAY, self::MINUTES_PER_HOUR);

        return $hours === 0 ? $days . 'd' : $days . 'd ' . $hours . 'h';
    }
}
