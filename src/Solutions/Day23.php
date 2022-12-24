<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\ImageOutput;
use AdventOfCode\Common\Solution\AbstractSolution;
use AdventOfCode\Common\Utils\MapUtils;

class Day23 extends AbstractSolution
{
    protected array $dirs = [
        'N'  => [0, -1],
        'NE' => [1, -1],
        'E'  => [1, 0],
        'SE' => [1, 1],
        'S'  => [0, 1],
        'SW' => [-1, 1],
        'W'  => [-1, 0],
        'NW' => [-1, -1],
    ];

    protected array $dirGroups = [
        ['N', 'NE', 'NW'],
        ['S', 'SE', 'SW'],
        ['W', 'NW', 'SW'],
        ['E', 'NE', 'SE'],
    ];

    protected function solvePart1(): string
    {
        return $this->run(10);
    }

    protected function solvePart2(): string
    {
        return $this->run();
    }

    public function run(int $numRounds = PHP_INT_MAX): int
    {
        $min = [0, 0];
        $max = [0, 0];
        $map = [];
        foreach ($this->getInputLines() as $y => $row) {
            foreach (str_split($row) as $x => $value) {
                if ($value == '#') {
                    $map[$y][$x] = true;
                    $max[0] = max($max[0], $x);
                    $max[1] = max($max[1], $y);
                }
            }
        }
        // $this->render($map, 0);
        for ($i = 0; $i < $numRounds; $i++) {
            $newMap = [];
            $static = true;
            foreach ($map as $y => $row) {
                foreach ($row as $x => $tmp) {
                    if (!$tmp) {
                        // remainder of a collision happened here, ignore
                        continue;
                    }
                    $adj = [];
                    $hasAdj = false;
                    foreach ($this->dirs as $d => $dir) {
                        $occ = !empty($map[$y + $dir[1]][$x + $dir[0]]);
                        $hasAdj = $hasAdj || $occ;
                        $adj[$d] = $occ;
                    }
                    if (!$hasAdj) {
                        $newMap[$y][$x] = true;
                        continue;
                    }
                    $static = false;
                    // Try to move in each of the 4 direction groups (offset by $i % 3)
                    $moved = false;
                    for ($dg = 0; $dg < 4; $dg++) {
                        $dirs = $this->dirGroups[($i + $dg) % 4];
                        foreach ($dirs as $dir) {
                            if ($adj[$dir]) {
                                continue 2;
                            }
                        }
                        // echo "move $x, $y by " . implode(', ', $dirs[0]) . "\n";
                        $moved = true;
                        $dir = $this->dirs[$dirs[0]];
                        $nextPos = [$x + $dir[0], $y + $dir[1]];
                        if (isset($newMap[$nextPos[1]][$nextPos[0]])) {
                            $newMap[$y][$x] = true;
                            $old = $newMap[$nextPos[1]][$nextPos[0]];
                            if (is_array($old)) {
                                $newMap[$old[1]][$old[0]] = true;
                            }
                            $newMap[$nextPos[1]][$nextPos[0]] = false;
                        } else {
                            $newMap[$nextPos[1]][$nextPos[0]] = [$x, $y];
                            foreach ([0, 1] as $axis) {
                                if ($dir[$axis] < 0) {
                                    $min[$axis] = min($min[$axis], $nextPos[$axis]);
                                } elseif ($dir[$axis] > 0) {
                                    $max[$axis] = max($max[$axis], $nextPos[$axis]);
                                }
                            }
                        }
                        break;
                    }
                    if (!$moved) {
                        $newMap[$y][$x] = true;
                    }
                }
            }
            $map = array_filter($newMap);
            // $this->render($map, $i + 1);
            // Part 2 ends here
            if ($static) {
                // ImageOutput::pngSequenceToGif('var/out/23', 'aoc-23.gif');
                return $i + 1;
            }
        }
        // Part 1 ends here
        $width = $max[0] - $min[0] + 1;
        $height = $max[1] - $min[1] + 1;
        $totalOccupied = 0;
        foreach ($map as $row) {
            foreach ($row as $val) {
                $totalOccupied += $val ? 1 : 0;
            }
        }
        return ($width * $height) - $totalOccupied;
    }

    protected function render(array $map, int $step): void
    {
        $bounds = ['l' => -15, 't' => -14, 'r' => 127, 'b' => 127];
        $completeMap = MapUtils::createCompleteMap($map, $bounds, '.');
        $filename = 'var/out/23/' . str_pad($step, 10, '0', STR_PAD_LEFT) . '.png';
        ImageOutput::map($completeMap, $filename, 4, [
            '#' => [60, 60, 60],
            '.' => [160, 160, 200],
        ]);
    }
}
