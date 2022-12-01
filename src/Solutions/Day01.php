<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day01 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $elves = explode("\n\n", $this->rawInput);
        $calories = array_map(fn (string $foods): int => array_sum(explode("\n", $foods)), $elves);
        return max($calories);
    }

    protected function solvePart2(): string
    {
        $elves = explode("\n\n", $this->rawInput);
        $calories = array_map(fn (string $foods): int => array_sum(explode("\n", $foods)), $elves);
        rsort($calories);
        return $calories[0] + $calories[1] + $calories[2];
    }
}
