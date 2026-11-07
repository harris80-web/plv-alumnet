<?php

namespace App\Support;

/**
 * Generates the dummy-data artwork the seeders use: formal portrait
 * illustrations for profile pictures, and simple emblem logos for companies.
 * Everything is drawn with GD, so it runs on any PC with PHP.
 * These are drawn illustrations, not photographs.
 */
class SeedArt
{
    private const SKIN = [[238, 200, 172], [222, 178, 146], [196, 146, 110], [150, 104, 72], [112, 76, 52], [246, 218, 196]];
    private const SUITS = [[42, 46, 58], [28, 38, 66], [22, 22, 26], [70, 50, 40], [58, 62, 72], [36, 52, 40]];
    private const TIES = [[150, 30, 40], [30, 70, 140], [200, 160, 50], [40, 110, 90], [110, 40, 120], [60, 60, 66]];
    private const HAIR = [[24, 20, 18], [60, 40, 28], [100, 70, 40], [150, 140, 130], [40, 34, 30]];
    private const BACKS = [[196, 214, 226], [226, 220, 204], [206, 226, 214], [222, 206, 214], [200, 206, 224], [230, 230, 226]];
    private const BRAND = [[14, 15, 59], [29, 70, 164], [199, 61, 26], [14, 124, 91], [110, 58, 150], [168, 110, 25], [40, 40, 44], [0, 110, 140]];

    /**
     * Formal head-and-shoulders portrait. Drawn at 2x (800px) on a 400-unit
     * coordinate grid and scaled down, which smooths every edge.
     */
    public static function portrait(string $path, int $seed, string $gender): void
    {
        mt_srand($seed);
        $k = 2;                       // supersampling factor
        $S = 400 * $k;
        $img = imagecreatetruecolor($S, $S);
        imagealphablending($img, true);
        $c = fn (array $rgb, int $a = 0) => imagecolorallocatealpha($img, (int) max(0, min(255, $rgb[0])), (int) max(0, min(255, $rgb[1])), (int) max(0, min(255, $rgb[2])), $a);
        $E = fn ($x, $y, $w, $h, $col) => imagefilledellipse($img, (int) ($x * $k), (int) ($y * $k), (int) ($w * $k), (int) ($h * $k), $col);
        $P = function (array $pts, $col) use ($img, $k) {
            imagefilledpolygon($img, array_map(fn ($v) => (int) ($v * $k), $pts), intdiv(count($pts), 2), $col);
        };
        $A = fn ($x, $y, $w, $h, $s, $e, $col, $t = 2) => [imagesetthickness($img, $t * $k), imagearc($img, (int) ($x * $k), (int) ($y * $k), (int) ($w * $k), (int) ($h * $k), $s, $e, $col), imagesetthickness($img, 1)];
        $mul = fn (array $rgb, float $f) => [$rgb[0] * $f, $rgb[1] * $f, $rgb[2] * $f];

        $female = $gender === 'female';

        // Studio backdrop: soft gradient with a light pool behind the head.
        $bg = self::pick(self::BACKS);
        for ($y = 0; $y < $S; $y++) {
            $t = $y / $S;
            imageline($img, 0, $y, $S, $y, $c($mul($bg, 1.0 - 0.18 * $t)));
        }
        for ($r = 0; $r < 8; $r++) $E(200, 170, 380 - $r * 30, 380 - $r * 30, $c([255, 255, 255], 120));

        $skin = self::pick(self::SKIN);
        $skinShade = $mul($skin, 0.84);
        $hairRgb = self::pick(self::HAIR);
        $suit = self::pick(self::SUITS);

        // Long hair falls behind the shoulders (drawn first).
        $longHair = $female && mt_rand(0, 4) > 0;
        if ($longHair) {
            $P([108, 170, 292, 170, 318, 330, 300, 360, 100, 360, 82, 330], $c($mul($hairRgb, 0.85)));
        }

        // Shoulders / jacket: a wide ellipse cut off by the frame.
        $E(200, 470, 400, 300, $c($suit));
        // Lapels (slightly lighter, catching the light).
        $P([162, 300, 200, 336, 176, 400, 120, 400, 128, 330], $c($mul($suit, 1.25)));
        $P([238, 300, 200, 336, 224, 400, 280, 400, 272, 330], $c($mul($suit, 1.12)));
        if ($female) {
            // Blouse V-neck and a fine necklace.
            $blouse = self::pick([[244, 244, 246], [236, 222, 214], [214, 226, 240], [250, 240, 230]]);
            $P([170, 298, 230, 298, 200, 372], $c($blouse));
            $A(200, 312, 56, 40, 20, 160, $c([214, 186, 120]), 1);
        } else {
            // Shirt collar and tie.
            $P([172, 296, 228, 296, 200, 352], $c([246, 246, 248]));
            $P([172, 296, 192, 296, 186, 318], $c([226, 226, 230]));
            $P([228, 296, 208, 296, 214, 318], $c([226, 226, 230]));
            $tie = self::pick(self::TIES);
            $P([192, 306, 208, 306, 204, 322, 196, 322], $c($mul($tie, 0.9)));
            $P([196, 322, 204, 322, 214, 400, 186, 400], $c($tie));
        }

        // Neck with a shadow under the jaw.
        $P([176, 228, 224, 228, 230, 300, 170, 300], $c($skinShade));
        $E(200, 252, 64, 30, $c($mul($skin, 0.72), 70));

        // Ears, head.
        $E(126, 186, 26, 42, $c($skinShade));
        $E(274, 186, 26, 42, $c($skinShade));
        $E(200, 180, 148, 184, $c($skin));
        // Form shading: darker on the far side, warm cheeks.
        $E(232, 196, 70, 150, $c($mul($skin, 0.82), 92));
        $E(164, 214, 30, 18, $c([230, 120, 110], $female ? 104 : 116));
        $E(236, 214, 30, 18, $c([230, 120, 110], $female ? 104 : 116));

        // Hair on top.
        $hairC = $c($hairRgb);
        $hairHi = $c($mul($hairRgb, 1.5), 90);
        $style = mt_rand(0, 2);
        if ($female) {
            imagefilledarc($img, 200 * $k, 150 * $k, 168 * $k, 150 * $k, 180, 360, $hairC, IMG_ARC_PIE);
            $P([118, 150, 136, 150, 128, 236, 116, 222], $hairC);            // side strands
            $P([282, 150, 264, 150, 272, 236, 284, 222], $hairC);
            if ($style === 0) $P([150, 108, 230, 112, 200, 150, 140, 158], $hairC); // side-swept fringe
            if (!$longHair) $E(200, 92, 70, 50, $hairC);                     // bun
            $E(176, 112, 60, 18, $hairHi);
        } else {
            imagefilledarc($img, 200 * $k, 156 * $k, 160 * $k, 140 * $k, 180, 360, $hairC, IMG_ARC_PIE);
            $P([122, 156, 132, 150, 132, 182, 124, 178], $hairC);            // sideburns
            $P([278, 156, 268, 150, 268, 182, 276, 178], $hairC);
            if ($style === 1) $P([150, 100, 240, 96, 214, 124, 160, 130], $hairC);  // side part volume
            if ($style === 2) $E(200, 100, 150, 30, $c($mul($hairRgb, 0.9)));      // short crop
            $E(170, 104, 50, 14, $hairHi);
        }

        // Eyes: white, iris, pupil, highlight, upper lid.
        $iris = self::pick([[70, 46, 30], [52, 36, 26], [90, 64, 40], [40, 30, 24]]);
        foreach ([166, 234] as $ex) {
            $E($ex, 190, 30, 14, $c([248, 246, 244]));
            $E($ex, 190, 13, 13, $c($iris));
            $E($ex, 190, 6, 6, $c([20, 16, 16]));
            $E($ex - 2, 187, 3, 3, $c([255, 255, 255]));
            $A($ex, 192, 32, 18, 195, 345, $c([40, 30, 28]), $female ? 2 : 1);
            if ($female) $A($ex + ($ex < 200 ? -14 : 14), 186, 6, 6, 200, 300, $c([30, 24, 22]), 1);
        }
        // Brows.
        $brow = $c($mul($hairRgb, 0.9));
        $A(166, 178, 40, 12, 200, 340, $brow, $female ? 2 : 3);
        $A(234, 178, 40, 12, 200, 340, $brow, $female ? 2 : 3);
        // Nose: soft shadow, nostrils.
        $P([200, 196, 206, 222, 196, 222], $c($mul($skin, 0.86), 80));
        $E(193, 223, 7, 4, $c($mul($skin, 0.6), 60));
        $E(207, 223, 7, 4, $c($mul($skin, 0.6), 60));
        // Mouth: lips plus a slight smile line.
        $lip = $female ? self::pick([[186, 76, 92], [170, 84, 84], [200, 100, 110]]) : $mul($skin, 0.72);
        $E(200, 244, 40, 10, $c($lip));
        $E(200, 250, 34, 10, $c($mul($lip, 1.08)));
        $A(200, 240, 44, 16, 20, 160, $c($mul($lip, 0.6)), 1);

        // Glasses on some people.
        if (mt_rand(0, 4) === 0) {
            $frame = $c([30, 30, 34]);
            foreach ([166, 234] as $ex) $A($ex, 190, 44, 32, 0, 360, $frame, 2);
            $A(200, 192, 22, 10, 200, 340, $frame, 2);
        }

        // Soften, then scale down for clean edges.
        imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
        $out = imagecreatetruecolor(400, 400);
        imagecopyresampled($out, $img, 0, 0, 0, 0, 400, 400, $S, $S);
        imagepng($out, $path, 6);
        imagedestroy($img);
        imagedestroy($out);
    }

    public static function logo(string $path, string $name, int $seed): void
    {
        mt_srand($seed);
        $size = 400;
        $img = imagecreatetruecolor($size, $size);
        imageantialias($img, true);
        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $size, $size, $bg);
        $brand = self::pick(self::BRAND);
        $accent = self::pick(self::BRAND);
        $c = imagecolorallocate($img, ...$brand);
        $a = imagecolorallocate($img, ...$accent);
        $shape = mt_rand(0, 4);
        switch ($shape) {
            case 0:
                imagefilledellipse($img, 200, 200, 300, 300, $c);
                imagefilledellipse($img, 200, 200, 240, 240, $bg);
                imagefilledellipse($img, 200, 200, 200, 200, $a);
                break;
            case 1:
                imagefilledpolygon($img, [200, 40, 350, 110, 330, 260, 200, 360, 70, 260, 50, 110], 6, $c);
                imagefilledpolygon($img, [200, 90, 290, 130, 280, 240, 200, 310, 120, 240, 110, 130], 6, $a);
                break;
            case 2:
                imagefilledrectangle($img, 60, 60, 340, 340, $c);
                imagefilledrectangle($img, 84, 84, 316, 316, $bg);
                imagefilledrectangle($img, 110, 110, 290, 290, $a);
                break;
            case 3:
                imagefilledpolygon($img, [200, 50, 340, 340, 60, 340], 3, $c);
                imagefilledpolygon($img, [200, 120, 280, 300, 120, 300], 3, $bg);
                break;
            default:
                for ($k = 0; $k < 6; $k++) {
                    $ang = $k * M_PI / 3;
                    imagefilledellipse($img, 200 + cos($ang) * 80, 200 + sin($ang) * 80, 130, 130, $k % 2 ? $c : $a);
                }
        }
        $initials = self::initials($name);
        $font = 'C:/Windows/Fonts/arialbd.ttf';
        $ink = $shape === 3 ? $c : imagecolorallocate($img, 255, 255, 255);
        if ($shape === 2 || $shape === 4) $ink = imagecolorallocate($img, 255, 255, 255);
        if (is_file($font)) {
            $box = imagettfbbox(92, 0, $font, $initials);
            $w = $box[2] - $box[0];
            $h = $box[1] - $box[7];
            imagettftext($img, 92, 0, (int) (200 - $w / 2), (int) (200 + $h / 2 - 8), $ink, $font, $initials);
        }
        imagepng($img, $path, 6);
        imagedestroy($img);
    }

    private static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim(preg_replace('/[^A-Za-z ]/', '', $name)));
        $skip = ['and', 'of', 'the', 'inc', 'corp', 'co', 'group', 'solutions', 'holdings', 'enterprises'];
        $letters = '';
        foreach ($words as $w) {
            if ($w === '' || in_array(strtolower($w), $skip, true)) continue;
            $letters .= strtoupper($w[0]);
            if (strlen($letters) >= 2) break;
        }

        return $letters !== '' ? $letters : 'PL';
    }

    private static function pick(array $list): array
    {
        return $list[mt_rand(0, count($list) - 1)];
    }
}
