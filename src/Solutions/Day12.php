<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\ImageOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day12 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->run();
    }

    protected function solvePart2(): string
    {
        return $this->run([
            'start'      => 'E',
            'startValue' => 'z',
            'end'        => 'S',
            'endValue'   => 'a',
            'part'       => 2,
        ]);
    }

    protected function run(array $config = []): string
    {
        // Parse input into map, start position, visited tiles and target tile
        list($map, $start, $target) = $this->parseInput($config);
        $positions = [$start];
        $visited = [$start[1] => [$start[0] => true]];
        $part = $config['part'] ?? 1;
        $dirs = [[0, -1], [0, 1], [1, 0], [-1, 0]];

        // Find that path
        $steps = 0;
        while ($positions) {
            // $this->drawMap($steps, $part, $map, $visited, $start, $part == 1 ? $target : null);
            $steps++;
            $newPositions = [];
            foreach ($positions as $position) {
                $current = $map[$position[1]][$position[0]];
                foreach ($dirs as $dir) {
                    $x = $position[0] + $dir[0];
                    $y = $position[1] + $dir[1];
                    if (!isset($map[$y][$x]) || isset($visited[$y][$x])) {
                        continue;
                    }
                    if ($part == 1 && $map[$y][$x] <= $current + 1) {
                        if ($map[$y][$x] == 26 && $x == $target[0] && $y == $target[1]) {
                            // ImageOutput::pngSequenceToGif('var/out/12/1', 'part1.gif');
                            return $steps;
                        }
                        $newPositions[] = [$x, $y];
                        $visited[$y][$x] = true;
                    } elseif ($part == 2 && $map[$y][$x] >= $current - 1) {
                        if ($map[$y][$x] == 1) {
                            // $this->drawMap($steps, $part, $map, $visited, $start, [$x, $y]);
                            // ImageOutput::pngSequenceToGif('var/out/12/2', 'part2.gif');
                            return $steps;
                        }
                        $newPositions[] = [$x, $y];
                        $visited[$y][$x] = true;
                    }
                }
            }
            $positions = $newPositions;
        }
        return 'nowhere to go!';
    }

    protected function drawMap(int $step, int $part, array $map, array $visited, array $start, ?array $end): void
    {
        $colors = [];
        for ($i = 1; $i <= 26; $i++) {
            $val = 0 + ((26 - $i) * 8);
            $colors[$i] = [$val, $val, $val];
        }
        for ($i = 1; $i <= 26; $i++) {
            $val = 100 + ((26 - $i) * 5);
            $colors[$i + 100] = [0, 0, $val];
        }
        foreach ($visited as $y => $row) {
            foreach ($row as $x => $val) {
                $map[$y][$x] += 100;
            }
        }
        $map[$start[1]][$start[0]] = 'S';
        if ($end) {
            $map[$end[1]][$end[0]] = 'E';
        }
        $colors['S'] = [100, 100, 255];
        $colors['E'] = [50, 50, 0];
        ImageOutput::map($map, 'var/out/12/' . $part . '/' . str_pad($step, 5, '0', STR_PAD_LEFT) . '.png', 4, $colors);
    }

    protected function parseInput(array $config): array
    {
        $map = array_map(fn(string $line) => str_split($line), $this->getInputLines());
        $start = [];
        $target = null;
        foreach ($map as $y => $xs) {
            foreach ($xs as $x => $val) {
                if ($val == ($config['start'] ?? 'S')) {
                    $start = [$x, $y];
                    $val = $config['startValue'] ?? 'a';
                } elseif ($val == ($config['end'] ?? 'E')) {
                    $target = [$x, $y];
                    $val = $config['endValue'] ?? 'z';
                }
                $map[$y][$x] = ord($val) - 96;
            }
        }
        return [$map, $start, $target];
    }
}
