<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day24 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        list($emptyMap, $blizzards) = $this->parseInput();
        return $this->run($emptyMap, $blizzards, new Vector2(1,0));
    }

    protected function solvePart2(): string
    {
        list($emptyMap, $blizzards) = $this->parseInput();
        $total = $this->run($emptyMap, $blizzards, new Vector2(1,0));
        $total += $this->run($emptyMap, $blizzards, new Vector2(count($emptyMap[0]) - 2, count($emptyMap) - 1));
        $total += $this->run($emptyMap, $blizzards, new Vector2(1,0));
        return $total;
    }

    private function run(array $emptyMap, array &$blizzards, Vector2 $position): int
    {
        $dirs = [
            new Vector2(0, 1),  // down
            new Vector2(0, -1), // up
            new Vector2(1, 0),  // left
            new Vector2(-1, 0), // right
            new Vector2(0, 0),  // no movement
        ];
        $min = new Vector2(1, 1);
        $max = new Vector2(count($emptyMap[0]) - 2, count($emptyMap) - 2);
        $targetY = $position->y == 0 ? $max->y + 1 : 0;
        // $maxAtPosition = least_common_multiple(count($emptyMap[0]) - 2, count($emptyMap) - 2);
        // $firstStepAtPosition = [$position->y => [$position->x => 0]];
        $positions = [$position];
        $step = 0;
        while (++$step) {
            // Move all blizzards
            $map = $emptyMap;
            $newPositions = [];
            foreach ($blizzards as $blizzard) {
                if ($blizzard->dir->x > 0) {
                    $blizzard->pos->x++;
                    if ($blizzard->pos->x > $max->x) $blizzard->pos->x = $min->x;
                } elseif ($blizzard->dir->x < 0) {
                    $blizzard->pos->x--;
                    if ($blizzard->pos->x < $min->x) $blizzard->pos->x = $max->x;
                } elseif ($blizzard->dir->y > 0) {
                    $blizzard->pos->y++;
                    if ($blizzard->pos->y > $max->y) $blizzard->pos->y = $min->y;
                } else {
                    $blizzard->pos->y--;
                    if ($blizzard->pos->y < $min->y) $blizzard->pos->y = $max->y;
                }
                $map[$blizzard->pos->y][$blizzard->pos->x] = false;
            }
            // Move all positions
            foreach ($positions as $pos) {
                foreach ($dirs as $dir) {
                    if (!empty($map[$pos->y + $dir->y][$pos->x + $dir->x])) {
                        $newPos = new Vector2($pos->x + $dir->x, $pos->y + $dir->y);
                        /*/ Don't allow going to this position if this means repeating history
                        if (isset($firstStepAtPosition[$newPos->y][$newPos->x])) {
                            if ($step > $firstStepAtPosition[$newPos->y][$newPos->x] + $maxAtPosition) {
                                echo "truncation!\n";
                                continue;
                            }
                        } else {
                            $firstStepAtPosition[$newPos->y][$newPos->x] = $step;
                        } //*/
                        // Check whether we're at the target
                        if ($newPos->y == $targetY) {
                            return $step;
                        }
                        $newPositions[$newPos->x . ',' . $newPos->y] = $newPos;
                    }}
            }
            $positions = $newPositions;
        }
        return -1;
    }

    private function parseInput(): array
    {
        $map = [];
        $blizzards = [];
        foreach ($this->getInputLines() as $y => $row) {
            foreach (str_split($row) as $x => $value) {
                $blizzardDir = null;
                $map[$y][$x] = true;
                switch ($value) {
                    case '#':
                        $map[$y][$x] = false;
                        break;
                    case '>':
                        $blizzardDir = new Vector2(1, 0);
                        break;
                    case '<':
                        $blizzardDir = new Vector2(-1, 0);
                        break;
                    case '^':
                        $blizzardDir = new Vector2(0, -1);
                        break;
                    case 'v':
                        $blizzardDir = new Vector2(0, 1);
                        break;
                }
                if ($blizzardDir) {
                    $blizzard = new Blizzard(new Vector2($x, $y), $blizzardDir);
                    $blizzards[] = $blizzard;
                }
            }
        }
        return [$map, $blizzards];
    }
}

class Blizzard
{
    public function __construct(
        public Vector2 $pos,
        public readonly Vector2 $dir,
    ) {
    }
}

class Vector2
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }
}