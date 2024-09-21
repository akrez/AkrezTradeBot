<?php

namespace App\Support;

class Chart
{
    public static function generate($data, $imageWidth, $imageHeight, $margins)
    {
        // Grid dimensions and placement within image
        $gridTop = $margins[0];
        $gridLeft = $margins[3];
        $gridBottom = $imageHeight - $margins[2];
        $gridRight = $imageWidth - $margins[1];
        $gridHeight = $gridBottom - $gridTop;
        $gridWidth = $gridRight - $gridLeft;

        // Bar and line width
        $lineWidth = 2;

        // Font settings

        $font = storage_path('OpenSans-Regular.ttf');
        $fontSize = 10;

        // Margin between label and axis
        $labelMargin = ($margins[2] - $fontSize) / 2;

        // Max value on y-axis
        $yMaxValue = max($data);

        $yMinValue = min($data);

        // Init image
        $chart = imagecreate($imageWidth, $imageHeight);

        // Setup colors
        $backgroundColor = imagecolorallocate($chart, 255, 255, 255);
        $axisColor = imagecolorallocate($chart, 85, 85, 85);
        $labelColor = $axisColor;
        $gridColor = imagecolorallocate($chart, 212, 212, 212);
        $barColor = imagecolorallocate($chart, 47, 133, 217);

        imagefill($chart, 0, 0, $backgroundColor);

        imagesetthickness($chart, $lineWidth);

        // Draw the bars with labels

        $barSpacing = $gridWidth / count($data);
        $itemX = $gridLeft + $barSpacing / 2;

        $x0 = null;
        $y0 = null;

        foreach ($data as $key => $value) {

            $s = ($gridHeight / ($yMaxValue - $yMinValue));
            $v = ($value - $yMinValue) * $s;
            $y = $gridBottom - $v;
            $x = $itemX;

            // draw line chart
            if ($x0 and $y0) {
                imageline($chart, $x0, $y0, $x, $y, $barColor);
            }
            $x0 = $x;
            $y0 = $y;

            // draw the line
            /////////////imageline($chart, $gridLeft, $y, $gridRight, $y, $gridColor);

            // draw right aligned label
            $labelBox = imagettfbbox($fontSize, 0, $font, strval($value));
            $labelWidth = $labelBox[4] - $labelBox[0];

            $labelX = $gridLeft - $labelWidth - $labelMargin;
            $labelY = $y + $fontSize / 2;

            imagettftext($chart, $fontSize, 0, $labelX, $labelY, $labelColor, $font, strval($value));

            // draw the label

            $labelBox = imagettfbbox($fontSize, 0, $font, $key);
            $labelWidth = $labelBox[4] - $labelBox[0];

            $labelX = $itemX - $labelWidth / 2;
            $labelY = $gridBottom + $labelMargin + $fontSize;

            imagettftext($chart, $fontSize, 0, $labelX, $labelY, $labelColor, $font, $key);

            $itemX += $barSpacing;
        }

        imageline($chart, $gridLeft, $gridTop, $gridLeft, $gridBottom, $axisColor);
        imageline($chart, $gridLeft, $gridBottom, $gridRight, $gridBottom, $axisColor);

        return $chart;
    }
}
