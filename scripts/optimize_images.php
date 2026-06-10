<?php

$root = dirname(__DIR__);
$outputDir = $root . '/public/img/optimized';

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required.\n");
    exit(1);
}

if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Cannot create output directory: {$outputDir}\n");
    exit(1);
}

$images = [
    ['source' => 'public/img/about.jpg', 'target' => 'about-hero.jpg', 'max' => 1280, 'quality' => 78],
    ['source' => 'public/img/background.png', 'target' => 'background.jpg', 'max' => 1440, 'quality' => 76],
    ['source' => 'public/img/background-trangchu.png', 'target' => 'background-trangchu.jpg', 'max' => 1440, 'quality' => 76],
    ['source' => 'public/img/background-trangconlai.png', 'target' => 'background-trangconlai.jpg', 'max' => 1600, 'quality' => 76],
    ['source' => 'public/img/background-chantrang.png', 'target' => 'background-chantrang.jpg', 'max' => 1600, 'quality' => 76],
    ['source' => 'public/img/nen-ct.png', 'target' => 'nen-ct.jpg', 'max' => 1440, 'quality' => 76],
    ['source' => 'public/img/nen-pho-ct.png', 'target' => 'nen-pho-ct.jpg', 'max' => 1200, 'quality' => 76],
    ['source' => 'public/img/hoi-phu-nu.png', 'target' => 'hoi-phu-nu.jpg', 'max' => 900, 'quality' => 78],
];

foreach ($images as $image) {
    $sourcePath = $root . '/' . $image['source'];
    $targetPath = $outputDir . '/' . $image['target'];

    if (!is_file($sourcePath)) {
        echo "skip missing {$image['source']}\n";
        continue;
    }

    $source = imagecreatefromstring((string)file_get_contents($sourcePath));
    if (!$source) {
        echo "skip unreadable {$image['source']}\n";
        continue;
    }

    $width = imagesx($source);
    $height = imagesy($source);
    $scale = min(1, (int)$image['max'] / max($width, $height));
    $newWidth = max(1, (int)round($width * $scale));
    $newHeight = max(1, (int)round($height * $scale));

    $canvas = imagecreatetruecolor($newWidth, $newHeight);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    imageinterlace($canvas, true);
    imagejpeg($canvas, $targetPath, (int)$image['quality']);

    clearstatcache(true, $targetPath);
    printf(
        "%s -> public/img/optimized/%s (%dx%d, %s)\n",
        $image['source'],
        $image['target'],
        $newWidth,
        $newHeight,
        formatBytes((int)filesize($targetPath))
    );

    unset($source, $canvas);
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . 'M';
    }

    return round($bytes / 1024) . 'K';
}
