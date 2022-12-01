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
}
