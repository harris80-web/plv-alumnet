<?php

namespace App\Support;

/**
 * Builds a tiny, valid one-page PDF from plain text lines — used by the
 * seeder for dummy company registration documents and uploaded resumes, so
 * "View document" / "View resume" links open a real file. Standard Helvetica,
 * no external libraries.
 */
class SimplePdf
{
    /**
     * @param  array<int, array{0:string,1?:int,2?:bool}>  $lines  [text, fontSize = 11, bold = false]
     */
    public static function make(array $lines): string
    {
        $stream = "BT\n";
        $y = 780;
        foreach ($lines as $line) {
            [$text, $size, $bold] = [$line[0], $line[1] ?? 11, $line[2] ?? false];
            if ($text === '') { $y -= $size; continue; }
            foreach (self::wrap($text, $size) as $part) {
                $stream .= sprintf("/%s %d Tf 1 0 0 1 56 %d Tm (%s) Tj\n", $bold ? 'F2' : 'F1', $size, $y, self::escape($part));
                $y -= (int) round($size * 1.45);
                if ($y < 60) break 2;
            }
        }
        $stream .= "ET\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }

    private static function wrap(string $text, int $size): array
    {
        $max = (int) floor(500 / ($size * 0.5));   // rough Helvetica character budget per line
        return explode("\n", wordwrap($text, $max, "\n", true));
    }

    private static function escape(string $text): string
    {
        // Helvetica in a basic PDF is Latin-1 only.
        $text = strtr($text, ['₱' => 'PHP ', '–' => '-', '—' => '-', '’' => "'", '“' => '"', '”' => '"', 'ñ' => 'n', 'Ñ' => 'N']);
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
