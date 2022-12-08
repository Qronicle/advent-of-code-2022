<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day08 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $map = $this->parseInput();
        $width = count($map[0]);
        $height = count($map);
        $numFound = $width * 2 + $height * 2 - 4;
        $maxTop = array_fill(0, $width, 0);
        for ($y = 1; $y < $height - 1; $y++) {
            array_walk($maxTop, fn(int &$max, int $x) => $max = max($max, $map[$y-1][$x]));
            $maxLeft = 0;
            for ($x = 1; $x < $width - 1; $x++) {
                $maxLeft = max($maxLeft, $map[$y][$x - 1]);
                // Quick check for left and top
                if ($maxLeft < $map[$y][$x] || $maxTop[$x] < $map[$y][$x]) {
                    $numFound++;
                    continue;
                }
                // Check right
                if (max(array_slice($map[$y], $x + 1)) < $map[$y][$x]) {
                    $numFound++;
                    continue;
                }
                // Check below
                if (max_vertical_slice($map, $y + 1, $x) < $map[$y][$x]) {
                    $numFound++;
                }
            }
        }
        return $numFound;
    }

    protected function solvePart2(): string
    {
        $map = $this->parseInput();
        $width = count($map[0]);
        $height = count($map);
        $maxScore = 0;
        // Since all trees at the side have a 0 viewing distance, we can keep ignoring those
        for ($y = 1; $y < $height - 1; $y++) {
            for ($x = 1; $x < $width - 1; $x++) {
                $score = 1;
                // Look right
                $dist = 0;
                for ($i = $x + 1; $i < $width; $i++) {
                    $dist++;
                    if ($map[$y][$x] <= $map[$y][$i]) {
                        break;
                    }
                }
                $score *= $dist;
                // Look left
                $dist = 0;
                for ($i = $x - 1; $i >= 0; $i--) {
                    $dist++;
                    if ($map[$y][$x] <= $map[$y][$i]) {
                        break;
                    }
                }
                $score *= $dist;
                // Look down
                $dist = 0;
                for ($i = $y + 1; $i < $height; $i++) {
                    $dist++;
                    if ($map[$y][$x] <= $map[$i][$x]) {
                        break;
                    }
                }
                $score *= $dist;
                // Look up
                $dist = 0;
                for ($i = $y - 1; $i >= 0; $i--) {
                    $dist++;
                    if ($map[$y][$x] <= $map[$i][$x]) {
                        break;
                    }
                }
                $score *= $dist;
                $maxScore = max($score, $maxScore);
            }
        }
        return $maxScore;
    }

    protected function parseInput(): array
    {
        return array_map(fn (string $row) => str_split($row), $this->getInputLines());
    }
}

function max_vertical_slice(array $map, int $offset, int $x): int
{
    $max = 0;
    $c = count($map[$x]);
    for ($y = $offset; $y < $c; $y++) {
        $max = $map[$y][$x] > $max ? $map[$y][$x] : $max;
    }
    return $max;
}
