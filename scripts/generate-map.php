<?php

declare(strict_types=1);

/**
 * Jednorázový generátor statické mapy z OpenStreetMap dlaždic.
 * Vyznačí trasu od parkoviště (Maxihit) ke vchodu (Hernice, Nádražní 1141/44).
 * Výstup: public/images/email/mapa-hernice.png — lze ručně doladit / přegenerovat.
 *
 * Spuštění:  php scripts/generate-map.php
 */

$zoom = 18;
$tileSize = 256;
$W = 700;
$H = 460;

// GPS body (lat, lon)
$park  = [49.55648074583308, 15.939489137954505]; // parkoviště u Maxihit
$venue = [49.556698141854696, 15.940283570095627]; // vchod Hernice

$lonToX = static fn (float $lon): float => (($lon + 180) / 360) * (2 ** $zoom);
$latToY = static function (float $lat) use ($zoom): float {
    $r = deg2rad($lat);
    return (1 - log(tan($r) + 1 / cos($r)) / M_PI) / 2 * (2 ** $zoom);
};

// střed mezi body v globálních pixelech
$centerPxX = (($lonToX($park[1]) + $lonToX($venue[1])) / 2) * $tileSize;
$centerPxY = (($latToY($park[0]) + $latToY($venue[0])) / 2) * $tileSize;

$originX = $centerPxX - $W / 2;
$originY = $centerPxY - $H / 2;

$canvas = imagecreatetruecolor($W, $H);

$minTileX = (int) floor($originX / $tileSize);
$maxTileX = (int) floor(($originX + $W) / $tileSize);
$minTileY = (int) floor($originY / $tileSize);
$maxTileY = (int) floor(($originY + $H) / $tileSize);

$ctx = stream_context_create(['http' => [
    'header'  => "User-Agent: ZdarAI-portal/1.0 (ai@nmail.mtgforfun.cz)\r\n",
    'timeout' => 15,
]]);

for ($tx = $minTileX; $tx <= $maxTileX; $tx++) {
    for ($ty = $minTileY; $ty <= $maxTileY; $ty++) {
        $url = "https://tile.openstreetmap.org/{$zoom}/{$tx}/{$ty}.png";
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            fwrite(STDERR, "VAROVÁNÍ: nelze stáhnout {$url}\n");
            continue;
        }
        $tile = imagecreatefromstring($data);
        if ($tile === false) {
            continue;
        }
        $destX = (int) round($tx * $tileSize - $originX);
        $destY = (int) round($ty * $tileSize - $originY);
        imagecopy($canvas, $tile, $destX, $destY, 0, 0, $tileSize, $tileSize);
        imagedestroy($tile);
    }
}

// pixelová pozice bodu na plátně
$toPx = static function (array $p) use ($lonToX, $latToY, $tileSize, $originX, $originY): array {
    return [
        (int) round($lonToX($p[1]) * $tileSize - $originX),
        (int) round($latToY($p[0]) * $tileSize - $originY),
    ];
};

[$px, $py] = $toPx($park);
[$vx, $vy] = $toPx($venue);

// barvy
$green   = imagecolorallocate($canvas, 22, 163, 74);   // #16a34a
$white   = imagecolorallocate($canvas, 255, 255, 255);
$dark    = imagecolorallocate($canvas, 17, 24, 39);
$blue    = imagecolorallocate($canvas, 37, 99, 235);
$red     = imagecolorallocate($canvas, 220, 38, 38);

// pomocná funkce: marker = vyplněný kruh s obrysem + písmeno
$marker = static function ($img, int $x, int $y, $fill, $border, string $letter) use ($white): void {
    $r = 13;
    imagefilledellipse($img, $x, $y, $r * 2, $r * 2, $fill);
    imagesetthickness($img, 2);
    imageellipse($img, $x, $y, $r * 2, $r * 2, $white);
    imagesetthickness($img, 1);
    imagestring($img, 5, $x - 4, $y - 8, $letter, $white);
    unset($border);
};

// popisek s podkladovým boxem (ASCII kvůli built-in fontu)
$label = static function ($img, int $x, int $y, string $text, $bg) use ($white, $dark): void {
    $w = imagefontwidth(3) * strlen($text) + 10;
    $h = imagefontheight(3) + 6;
    imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $bg);
    imagerectangle($img, $x, $y, $x + $w, $y + $h, $white);
    imagestring($img, 3, $x + 5, $y + 3, $text, $white);
    unset($dark);
};

$marker($canvas, $px, $py, $blue, $white, 'P');
$marker($canvas, $vx, $vy, $red, $white, 'C');

$label($canvas, $px - 8, $py + 18, 'PARKOVISTE (u Maxihit)', $blue);
$label($canvas, $vx - 8, $vy - 30, 'VCHOD - Hernice', $red);

// atribuce OSM (povinné)
$attr = '(c) OpenStreetMap';
$aw = imagefontwidth(2) * strlen($attr) + 8;
imagefilledrectangle($canvas, $W - $aw - 2, $H - 18, $W - 2, $H - 2, $white);
imagestring($canvas, 2, $W - $aw + 2, $H - 16, $attr, $dark);

$out = __DIR__ . '/../public/images/email/mapa-hernice.png';
@mkdir(dirname($out), 0775, true);
imagepng($canvas, $out);
imagedestroy($canvas);

echo "Mapa uložena: {$out}\n";
