<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\TextOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day09 extends AbstractSolution
{
    protected array $dirs = [
        'U' => [0, -1],
        'D' => [0, 1],
        'R' => [1, 0],
        'L' => [-1, 0],
    ];

    protected function solvePart1(): string
    {
        return $this->run(2);
    }

    protected function solvePart2(): string
    {
        return $this->run(10);
    }

    protected function run(int $ropeLength): int
    {
        $rope = array_map(fn() => new Vector2(), array_fill(0, $ropeLength, null));
        $lastRopeIndex = $ropeLength - 1;
        $visited['0,0'] = true;

        foreach ($this->getInputLines() as $instruction) {
            list($dir, $count) = explode(' ', $instruction);
            for ($step = 0; $step < $count; $step++) {
                $rope[0]->move(...$this->dirs[$dir]);
                for ($i = 1; $i < $ropeLength; $i++) {
                    if ($rope[$i]->isFarAwayFrom($rope[$i - 1])) {
                        $rope[$i]->moveTo($rope[$i - 1]);
                        if ($i == $lastRopeIndex) {
                            $visited[$rope[$i]->x . ',' . $rope[$i]->y] = true;
                        }
                    }
                }
            }
        }

        return count($visited);
    }

    protected function renderState(array $rope): void
    {
        $map = array_fill(-4, 5, array_fill(0, 6, '.'));
        $map[0][0] = 's';
        for ($i = count($rope) - 1; $i >= 0; $i--) {
            $map[$rope[$i]->y][$rope[$i]->x] = $i;
        }
        echo TextOutput::map2d($map);
    }
}

class Vector2
{
    public function __construct(
        public int $x = 0,
        public int $y = 0,
    ) {
    }

    public function move(int $x, int $y): void
    {
        $this->x += $x;
        $this->y += $y;
    }

    public function isFarAwayFrom(Vector2 $target, int $dist = 1): bool
    {
        return abs($this->x - $target->x) > 1 || abs($this->y - $target->y) > $dist;
    }

    public function moveTo(Vector2 $target): void
    {
        $x = ($this->x + $target->x) / 2;
        $this->x = floor($x) == $target->x ? $target->x : ceil($x);
        $y = ($this->y + $target->y) / 2;
        $this->y = floor($y) == $target->y ? $target->y : ceil($y);
    }
}