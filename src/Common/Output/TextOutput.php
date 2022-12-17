<?php

namespace AdventOfCode\Common\Output;

/**
 * Class TextOutput
 *
 * @package AdventOfCode\Common\Output
 * @author  Ruud Seberechts
 */
class TextOutput
{
    public static function map2d(array $map): string
    {
        $output = '';
        foreach ($map as $row) {
            $output .= implode('', $row) . "\n";
        }
        return $output;
    }

    public static function incompleteMap(
        array $partialMap,
        array $bounds,
        string $void = ' ',
        ?array $extra = null,
        bool $reverseY = false
    ) {
        $l = $bounds['l'] ?? $bounds['x'] ?? 0;
        $r = $bounds['r'] ?? $l + $bounds['w'];
        $w = $bounds['w'] ?? $r - $l;
        $t = $bounds['t'] ?? $bounds['y'] ?? 0;
        $b = $bounds['b'] ?? $t + $bounds['h'];
        $h = $bounds['h'] ?? $b - $t;
        $map = array_fill($t, $h, array_fill($l, $w, $void));
        foreach ($partialMap as $y => $row) {
            foreach ($row as $x => $value) {
                $map[$y][$x] = is_string($value) ? $value : ($value ? '#' : '.');
            }
        }
        foreach ($extra ?? [] as $char => $extraInfo) {
            if (is_numeric(key($extraInfo))) {
                $extraInfo = ['coords' => $extraInfo];
            }
            $char = $extraInfo['char'] ?? $char;
            $coords = $extraInfo['coords'];
            $offset = $extraInfo['offset'] ?? [0, 0];
            foreach ($coords as $crds) {
                $map[$crds[1] + $offset[1]][$crds[0] + $offset[0]] = $char;
            }
        }
        if ($reverseY) {
            $map = array_reverse($map);
        }
        return self::map2d($map);
    }
}
