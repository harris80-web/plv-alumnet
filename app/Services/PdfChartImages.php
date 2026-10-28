<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Chart images posted by the "EXPORT PDF" buttons. Each page sends its
 * Chart.js canvases as a `charts` JSON field: {"canvasId": "data:image/...;base64,..."}.
 *
 * Only inline JPEG/PNG data is kept, never a path or URL for DomPDF to load.
 * The pages send JPEGs (see partials/chart-pdf-image) because DomPDF needs the
 * PHP GD extension for PNGs, so a PNG is only kept when GD is installed.
 * A chart missing from the result is shown as a table in the PDF instead.
 */
class PdfChartImages
{
    private const SIGNATURES = ['jpeg' => "\xFF\xD8\xFF", 'png' => "\x89PNG"];

    /** @param  string[]|null  $only  canvas ids to accept (null = any) */
    public static function fromRequest(Request $request, ?array $only = null): array
    {
        $charts = json_decode((string) $request->input('charts', '{}'), true);
        if (! is_array($charts)) {
            return [];
        }
        if ($only !== null) {
            $charts = array_intersect_key($charts, array_flip($only));
        }

        return array_filter($charts, [self::class, 'isUsableImage']);
    }

    private static function isUsableImage($src): bool
    {
        if (! is_string($src) || ! preg_match('#^data:image/(jpeg|png);base64,([A-Za-z0-9+/]++={0,2})$#', $src, $m)) {
            return false;
        }
        if ($m[1] === 'png' && ! function_exists('imagecreatefrompng')) {
            return false;
        }

        // The data must really be that kind of image, or DomPDF would fail on it.
        return str_starts_with((string) base64_decode(substr($m[2], 0, 8)), self::SIGNATURES[$m[1]]);
    }
}
