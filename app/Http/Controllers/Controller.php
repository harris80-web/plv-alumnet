<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Reads a rows-per-page value off the request (used by the shared
     * table-pagination-bar partial's rows-per-page select) and clamps it to
     * a known-good set of options so a tampered/garbage query param can't
     * force an oversized ->paginate() call. Falls back to $default (each
     * caller's pre-existing hardcoded page size) when the param is absent
     * or not one of the allowed choices, so behavior is unchanged until an
     * admin actually picks a different page size.
     */
    protected function resolvePerPage(int $default, string $param = 'per_page', array $allowed = [5, 10, 20, 50]): int
    {
        $requested = (int) request($param, $default);

        return in_array($requested, $allowed, true) ? $requested : $default;
    }
}
