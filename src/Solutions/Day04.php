<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day04 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $numContaining = 0;
        foreach ($this->getInputLines() as $line) {
            $sections = array_map(fn(string $s) => explode('-', $s), explode(',', $line));
            // Make sure section 1 is always the largest
            $size1 = $sections[0][1] - $sections[0][0];
            $size2 = $sections[1][1] - $sections[1][0];
            if ($size2 > $size1) {
                $tmp = $sections[0];
                $sections[0] = $sections[1];
                $sections[1] = $tmp;
            }
            $numContaining += ($sections[1][0] >= $sections[0][0] && $sections[1][1] <= $sections[0][1]) ? 1 : 0;
        }
        return $numContaining;
    }

    protected function solvePart2(): string
    {
        $numOverlap = 0;
        foreach ($this->getInputLines() as $line) {
            $sections = array_map(fn(string $s) => explode('-', $s), explode(',', $line));
            if ($sections[0][0] <= $sections[1][0]) {
                $numOverlap += ($sections[0][1] >= $sections[1][0]);
                continue;
            }
            if ($sections[0][1] >= $sections[1][0]) {
                $numOverlap += ($sections[0][0] <= $sections[1][1]);
            }
        }
        return $numOverlap;
    }
}
