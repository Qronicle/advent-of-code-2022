<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;

class Day06 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->findMarker(4);
    }

    protected function solvePart2(): string
    {
        return $this->findMarker(14);
    }

    protected function findMarker(int $length): int
    {
        $string = $this->rawInput;
        $chars = str_split(substr($string, 0, $length - 1));
        $len = strlen($string);
        for ($i = $length - 1; $i < $len; $i++) {
            $chars[] = $string[$i];
            if (count(array_unique($chars)) == $length) {
                return $i + 1;
            }
            array_shift($chars);
        }
        throw new Exception('No marker found');
    }
}
