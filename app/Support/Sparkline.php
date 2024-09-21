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

    public function generate($data, $w, $h, $line = '000000', $fill = 'ffffff', $back = 'ffffff')
    {
        $w = floor($w);
        $h = floor($h);
        $t = 2.5;
        $s = 4;

        $w *= $s;
        $h *= $s;
        $t *= $s;

        $data = (count($data) < 2) ? array_fill(0, 2, $data[0]) : $data;
        $count = count($data);
        $step = $w / ($count - 1);

        $min = min($data);
        $max = max($data);
        if ($max != $min) {
            foreach ($data as $k => $v) {
                $data[$k] -= $min;
            }
            $max = max($data);
        }

        $im = imagecreatetruecolor($w, $h);
        [$r, $g, $b] = $this->hexToRgb($back);
        $bg = imagecolorallocate($im, $r, $g, $b);
        [$r, $g, $b] = $this->hexToRgb($line);
        $fg = imagecolorallocate($im, $r, $g, $b);
        [$r, $g, $b] = $this->hexToRgb($fill);
        $lg = imagecolorallocate($im, $r, $g, $b);
        imagefill($im, 0, 0, $bg);

        imagesetthickness($im, $t);

        foreach ($data as $k => $v) {
            $v = $v > 0 ? round($v / $max * $h) : 0;
            $data[$k] = max($s, min($v, $h - $s));
        }

        $x1 = 0;
        $y1 = $h - $data[0];
        $line = [];
        $poly = [0, $h + 50, $x1, $y1];
        for ($i = 1; $i < $count; $i++) {
            $x2 = $x1 + $step;
            $y2 = $h - $data[$i];
            array_push($line, [$x1, $y1, $x2, $y2]);
            array_push($poly, $x2, $y2);
            $x1 = $x2;
            $y1 = $y2;
        }
        array_push($poly, $x2, $h + 50);

        imagefilledpolygon($im, $poly, $count + 2, $lg);

        foreach ($line as $k => $v) {
            [$x1, $y1, $x2, $y2] = $v;
            imageline($im, $x1, $y1, $x2, $y2, $fg);
        }

        $om = imagecreatetruecolor($w / $s, $h / $s);
        imagecopyresampled($om, $im, 0, 0, 0, 0, $w / $s, $h / $s, $w, $h);
        imagedestroy($im);

        return $om;
    }
}
