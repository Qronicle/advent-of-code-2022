<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day18 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $blocks = [];
        $sides = 0;
        foreach ($this->getInputLines() as $coords) {
            list($x, $y, $z) = explode(',', $coords);
            $sides += 6;
            foreach ($blocks as $tz => $zBlocks) {
                if (($zDist = abs($tz - $z)) > 1) {
                    continue;
                }
                foreach ($zBlocks as $ty => $yBlocks) {
                    if ($zDist + ($yDist = abs($ty - $y)) > 1) {
                        continue;
                    }
                    foreach ($yBlocks as $tx => $bla) {
                        if ($zDist + $yDist + abs($tx - $x) == 1) {
                            $sides -= 2;
                        }
                    }
                }
            }
            $blocks[$z][$y][$x] = true;
        }
        return $sides;
    }

    protected function solvePart2(): string
    {
        // Create volume with min and max bounds
        $blocks = [];
        $min = null;
        $max = null;
        foreach ($this->getInputLines() as $coords) {
            $coords = explode(',', $coords);
            $blocks[$coords[2]][$coords[1]][$coords[0]] = true;
            if ($min) {
                $min->min(...$coords);
                $max->max(...$coords);
            } else {
                $min = new Vector3(...$coords);
                $max = new Vector3(...$coords);
            }
        }
        $min->add(-1, -1, -1);
        $max->add(1, 1, 1);

        // Water edge simulation baybay
        $dirs = [[0, -1, 0], [0, 1, 0], [1, 0, 0], [-1, 0, 0], [0, 0, 1], [0, 0, -1]];
        $sides = 0;
        $points = [[$min->x, $min->y, $min->z]];
        $visited = [$min->z => [$min->y => [$min->x => true]]];
        while ($points) {
            $newPoints = [];
            foreach ($points as $point) {
                foreach ($dirs as $dir) {
                    $tx = $point[0] + $dir[0];
                    $ty = $point[1] + $dir[1];
                    $tz = $point[2] + $dir[2];
                    // stop when out of bounds or already visited
                    if (
                        $tx < $min->x || $tx > $max->x || $ty < $min->y || $ty > $max->y
                        || $tz < $min->z || $tz > $max->z || isset($visited[$tz][$ty][$tx])
                    ) {
                        continue;
                    }
                    // add side when crosses to lava
                    if (isset($blocks[$tz][$ty][$tx])) {
                        $sides++;
                        continue;
                    }
                    // Move forward!
                    $visited[$tz][$ty][$tx] = true;
                    $newPoints[] = [$tx, $ty, $tz];
                }
            }
            $points = $newPoints;
        }
        return $sides;
    }
}

class Vector3
{
    public function __construct(
        public int $x = 0,
        public int $y = 0,
        public int $z = 0,
    ) {
    }

    public function min(int $x, int $y, int $z): void
    {
        $this->x = min($this->x, $x);
        $this->y = min($this->y, $y);
        $this->z = min($this->z, $z);
    }

    public function max(int $x, int $y, int $z): void
    {
        $this->x = max($this->x, $x);
        $this->y = max($this->y, $y);
        $this->z = max($this->z, $z);
    }

    public function add(int $x, int $y, int $z): self
    {
        $this->x += $x;
        $this->y += $y;
        $this->z += $z;
        return $this;
    }
}