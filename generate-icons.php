<?php
/**
 * Run this script once from CLI or browser to generate PWA icon PNGs.
 * php generate-icons.php
 */
$dir = __DIR__ . '/public/assets/icons';
if (!is_dir($dir)) mkdir($dir, 0755, true);

function makeIcon(string $outPath, int $size): void {
    $img = imagecreatetruecolor($size, $size);
    imageantialias($img, true);

    // Background — rounded square (indigo)
    $bg  = imagecolorallocate($img, 99, 102, 241);   // #6366f1
    $fg  = imagecolorallocate($img, 255, 255, 255);  // white

    imagefill($img, 0, 0, $bg);

    // Draw a simple "$" style timeline symbol: two horizontal bars + vertical
    $pad = (int)($size * 0.2);
    $lw  = max(2, (int)($size * 0.07));

    // Three horizontal lines (timeline columns)
    $barH   = $lw;
    $x1     = $pad;
    $x2     = $size - $pad;
    $midY   = (int)($size / 2);

    imagefilledrectangle($img, $x1, $midY - (int)($size*0.22), $x2, $midY - (int)($size*0.22) + $barH, $fg);
    imagefilledrectangle($img, $x1, $midY - (int)($barH/2),     $x2, $midY + (int)($barH/2),             $fg);
    imagefilledrectangle($img, $x1, $midY + (int)($size*0.22) - $barH, $x2, $midY + (int)($size*0.22), $fg);

    // Vertical dividers
    $gaps = 3;
    for ($i = 1; $i < $gaps; $i++) {
        $vx = (int)($x1 + ($x2 - $x1) * ($i / $gaps));
        imagefilledrectangle($img, $vx, $pad, $vx + $lw, $size - $pad, $fg);
    }

    imagepng($img, $outPath);
    imagedestroy($img);
    echo "Created: $outPath\n";
}

makeIcon("$dir/icon-192.png", 192);
makeIcon("$dir/icon-512.png", 512);
echo "Done.\n";
