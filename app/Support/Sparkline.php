<?php

namespace App\Support;

class Sparkline
{
    public function hexToRgb($hex)
    {
        $hex = ltrim(strtolower($hex), '#');
        $hex = isset($hex[3]) ? $hex : $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        $dec = hexdec($hex);

        return [0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec];
    }

    public function generate($points, $width, $height, $margin = [], $lineRgbColor = [])
    {
        $margin = $margin + [
            'top' => 0,
            'bottom' => 0,
            'left' => 0,
            'right' => 0,
        ];
        //
        $lineRgbColor = $lineRgbColor + [
            'red' => 0,
            'green' => 0,
            'blue' => 0,
        ];
        // Create an image
        $image = imagecreatetruecolor($width, $height);
        // Calculate the scaling factor for the Y values
        $minValue = min($points);
        $maxValue = max($points);
        $scaleFactor = ($height - $margin['top'] - $margin['bottom']) / ($maxValue == $minValue ? 1 : $maxValue - $minValue);
        // Allocate colors
        $backgroundColor = imagecolorallocate($image, 255, 255, 255); // White background
        $axisColor = imagecolorallocate($image, 0, 0, 0); // Black axes
        $lineColor = imagecolorallocate($image, $lineRgbColor['red'], $lineRgbColor['green'], $lineRgbColor['blue']); // Black line
        // Fill the background
        imagefill($image, 0, 0, $backgroundColor);
        // Draw X and Y axes
        imagesetthickness($image, 3);
        imageline($image, $margin['left'], $margin['top'], $margin['left'], $height - $margin['bottom'], $axisColor); // Y axis
        imageline($image, $margin['left'], $height - $margin['bottom'], $width - $margin['right'], $height - $margin['bottom'], $axisColor); // X axis
        // Draw lines between points
        imagesetthickness($image, 1);
        for ($i = 0; $i < count($points) - 1; $i++) {
            $x1 = $margin['left'] + ($i * (($width - $margin['left'] - $margin['right']) / (count($points) - 1))); // X position for point i
            $x2 = $margin['left'] + (($i + 1) * (($width - $margin['left'] - $margin['right']) / (count($points) - 1))); // X position for point i+1
            $y1 = $height - $margin['bottom'] - ($maxValue == $minValue ? $points[$i] / 2 : ($points[$i] - $minValue) * $scaleFactor);
            $y2 = $height - $margin['bottom'] - ($maxValue == $minValue ? $points[$i + 1] / 2 : ($points[$i + 1] - $minValue) * $scaleFactor);
            imageline($image, intval($x1), intval($y1), intval($x2), intval($y2), $lineColor);
        }//

        return $image;
    }
}
