<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;

class Day22 extends AbstractSolution
{
    public const DIR_RIGHT = 0;
    public const DIR_DOWN  = 1;
    public const DIR_LEFT  = 2;
    public const DIR_UP    = 3;

    protected function solvePart1(): string
    {
        list($rows, $cols, $route) = $this->parseInput();
        $dirs = [
            [1, 0],  // right
            [0, 1],  // down
            [-1, 0], // left
            [0, -1], // up
        ];
        $turns = [
            'L' => -1,
            'R' => 1,
        ];
        $dir = 0;
        $x = $rows[0]->min; // works for my input
        $y = 0;
        foreach ($route as $action) {
            if (is_numeric($action)) {
                if ($dirs[$dir][0] == 0) {
                    // move in column
                    $col = $cols[$x];
                    $y = $col->move($y, $dirs[$dir][1], $action);
                } else {
                    // move in row
                    $row = $rows[$y];
                    $x = $row->move($x, $dirs[$dir][0], $action);
                }
            } else {
                $dir = mod($dir + $turns[$action], 4);
            }
        }
        return 1000 * ($y + 1) + 4 * ($x + 1) + $dir;
    }

    protected function solvePart2(): string
    {
        list($rows, $cols, $route) = $this->parseInput();
        $dirs = [
            self::DIR_RIGHT => [1, 0],
            self::DIR_DOWN  => [0, 1],
            self::DIR_LEFT  => [-1, 0],
            self::DIR_UP    => [0, -1],
        ];
        $turns = [
            'L' => -1,
            'R' => 1,
        ];
        $sides = [
            [0, 1, 2],
            [0, 3, 0],
            [4, 5, 0],
            [6, 0, 0],
        ];
        $sideSize = count($cols) / 3;
        $panels = [];
        foreach ($sides as $y => $ySides) {
            foreach ($ySides as $x => $p) {
                if ($p == 0) continue;
                $panels[$p] = (object)[
                    'minX' => $sideSize * $x,
                    'maxX' => $sideSize * ($x + 1) - 1,
                    'minY' => $sideSize * $y,
                    'maxY' => $sideSize * ($y + 1) - 1,
                ];
            }
        }
        $dir = 0;
        $x = $rows[0]->min; // works for my input
        $y = 0;
        while ($route) {
            $action = array_shift($route);
            if (is_numeric($action)) {
                $horizontal = false;
                try {
                    if ($dirs[$dir][0] == 0) {
                        // move in column
                        $col = $cols[$x];
                        $y = $col->move3d($y, $dirs[$dir][1], $action);
                    } else {
                        // move in row
                        $horizontal = true;
                        $row = $rows[$y];
                        $x = $row->move3d($x, $dirs[$dir][0], $action);
                    }
                } catch (OutOfBoundsException $ex) {
                    if ($horizontal) {
                        $x = $ex->pos;
                    } else {
                        $y = $ex->pos;
                    }
                    $sx = floor($x / $sideSize);
                    $sy = floor($y / $sideSize);
                    $side = $sides[$sy][$sx];
                    $offset = $horizontal
                        ? $y - $panels[$side]->minY
                        : $x - $panels[$side]->minX;
                    switch ($side) {
                        case 1:
                            if ($horizontal) {
                                // always to the left => move to 4 (C)
                                $targetPanel = $panels[4];
                                $nextX = $targetPanel->minX;
                                $nextY = $targetPanel->maxY - $offset;
                                $nextDir = self::DIR_RIGHT;
                            } else {
                                // always to the top => move to 6 (A)
                                $targetPanel = $panels[6];
                                $nextX = $targetPanel->minX;
                                $nextY = $targetPanel->minY + $offset;
                                $nextDir = self::DIR_RIGHT;
                            }
                            break;
                        case 2:
                            if ($horizontal) {
                                // always to the right => move to 5 (D)
                                $targetPanel = $panels[5];
                                $nextX = $targetPanel->maxX;
                                $nextY = $targetPanel->maxY - $offset;
                                $nextDir = self::DIR_LEFT;
                            } else {
                                if ($ex->dir > 0) {
                                    // to the bottom => move to 3 (E)
                                    $targetPanel = $panels[3];
                                    $nextX = $targetPanel->maxX;
                                    $nextY = $targetPanel->minY + $offset;
                                    $nextDir = self::DIR_LEFT;
                                } else {
                                    // to the top => move to 6 (B)
                                    $targetPanel = $panels[6];
                                    $nextX = $targetPanel->minX + $offset;
                                    $nextY = $targetPanel->maxY;
                                    $nextDir = self::DIR_UP;
                                }
                            }
                            break;
                        case 3:
                            // always horizontal
                            if ($ex->dir > 0) {
                                // to the right => move to 2 (E)
                                $targetPanel = $panels[2];
                                $nextX = $targetPanel->minX + $offset;
                                $nextY = $targetPanel->maxY;
                                $nextDir = self::DIR_UP;
                            } else {
                                // to the left => move to 4 (F)
                                $targetPanel = $panels[4];
                                $nextX = $targetPanel->minX + $offset;
                                $nextY = $targetPanel->minY;
                                $nextDir = self::DIR_DOWN;
                            }
                            break;
                        case 4:
                            if ($horizontal) {
                                // always to the left => move to 1 (C)
                                $targetPanel = $panels[1];
                                $nextX = $targetPanel->minX;
                                $nextY = $targetPanel->maxY - $offset;
                                $nextDir = self::DIR_RIGHT;
                            } else {
                                // always to the top => move to 3 (F)
                                $targetPanel = $panels[3];
                                $nextX = $targetPanel->minX;
                                $nextY = $targetPanel->minY + $offset;
                                $nextDir = self::DIR_RIGHT;
                            }
                            break;
                        case 5:
                            if ($horizontal) {
                                // always to the right => move to 2 (D)
                                $targetPanel = $panels[2];
                                $nextX = $targetPanel->maxX;
                                $nextY = $targetPanel->maxY - $offset;
                                $nextDir = self::DIR_LEFT;
                            } else {
                                // always to the bottom => move to 6 (G)
                                $targetPanel = $panels[6];
                                $nextX = $targetPanel->maxX;
                                $nextY = $targetPanel->minY + $offset;
                                $nextDir = self::DIR_LEFT;
                            }
                            break;
                        case 6:
                            if ($horizontal) {
                                if ($ex->dir > 0) {
                                    // to the right => move to 5 (G)
                                    $targetPanel = $panels[5];
                                    $nextX = $targetPanel->minX + $offset;
                                    $nextY = $targetPanel->maxY;
                                    $nextDir = self::DIR_UP;
                                } else {
                                    // to the left => move to 1 (A)
                                    $targetPanel = $panels[1];
                                    $nextX = $targetPanel->minX + $offset;
                                    $nextY = $targetPanel->minY;
                                    $nextDir = self::DIR_DOWN;
                                }
                            } else {
                                // always to the bottom => move to 2 (B)
                                $targetPanel = $panels[2];
                                $nextX = $targetPanel->minX + $offset;
                                $nextY = $targetPanel->minY;
                                $nextDir = self::DIR_DOWN;
                            }
                            break;
                        default:
                            throw new Exception("Invalid move in direction {$ex->dir} from panel $side.");
                    }
                    if (!isset($rows[$nextY]->walls[$nextX])) {
                        $x = $nextX;
                        $y = $nextY;
                        $dir = $nextDir;
                        if ($ex->distance > 1) {
                            array_unshift($route, $ex->distance - 1);
                        }
                    }
                }
            } else {
                $dir = mod($dir + $turns[$action], 4);
            }
        }
        return 1000 * ($y + 1) + 4 * ($x + 1) + $dir;
    }

    protected function parseInput()
    {
        list($map, $routeStr) = explode("\n\n", $this->rawInput);

        // Parse map into cols/rows objects
        $map = array_map(fn(string $row) => str_split($row), explode("\n", $map));
        /** @var Row[] $cols */
        $cols = [];
        /** @var Row[] $rows */
        $rows = [];
        for ($x = 0; $x < count($map[0]); $x++) {
            $cols[$x] = new Row($x);
        }
        foreach ($map as $y => $mapRow) {
            $row = new Row($y);
            $rows[$y] = $row;
            foreach ($mapRow as $x => $value) {
                if ($value == ' ') {
                    continue;
                }
                $col = $cols[$x];
                if (!isset($col->min)) {
                    $col->min = $y;
                }
                if (!isset($row->min)) {
                    $row->min = $x;
                }
                $col->max = max($y, $col->max ?? 0);
                $row->max = max($x, $row->max ?? 0);
                if ($value == '#') {
                    $col->walls[$y] = $y;
                    $row->walls[$x] = $x;
                }
            }
        }

        // Parse route
        $route = str_split($routeStr);
        $prevIsNumber = false;
        for ($i = 0; $i < count($route); $i++) {
            $isNumber = is_numeric($route[$i]);
            if ($prevIsNumber && $isNumber) {
                $route[$i - 1] .= array_splice($route, $i, 1)[0];
                $i--;
            }
            $prevIsNumber = $isNumber;
        }

        return [
            $rows,
            $cols,
            $route,
        ];
    }
}

class Row
{
    public array $walls = [];
    public int $min;
    public int $max;

    public function __construct(
        public readonly int $index,
    ) {
    }

    public function move(int $pos, int $dir, int $dist): int
    {
        for ($step = 0; $step < $dist; $step++) {
            $pos += $dir;
            if (isset($this->walls[$pos])) {
                return $pos - $dir;
            }
            if ($pos < $this->min) {
                if (isset($this->walls[$this->max])) {
                    return $this->min;
                }
                $pos = $this->max;
            } elseif ($pos > $this->max) {
                if (isset($this->walls[$this->min])) {
                    return $this->max;
                }
                $pos = $this->min;
            }
        }
        return $pos;
    }

    public function move3d(int $pos, int $dir, int $dist): int
    {
        for ($step = 0; $step < $dist; $step++) {
            $pos += $dir;
            if (isset($this->walls[$pos])) {
                return $pos - $dir;
            }
            if ($pos < $this->min || $pos > $this->max) {
                throw new OutOfBoundsException($this, $pos - $dir, $dir, $dist - $step);
            }
        }
        return $pos;
    }
}

class OutOfBoundsException extends Exception
{
    public function __construct(
        public Row $row,
        public int $pos,
        public int $dir,
        public int $distance,
    ) {
        parent::__construct();
    }
}

//   MAP:
//
//      0 1 2 3 4 5 6 7 8
//
//  0         1 A 1 2 B 2
//  1         C 1 1 2 2 D
//  2         1 1 1 2 E 2
//  3         3 3 3
//  4         F 3 E
//  5         3 3 3
//  6   4 F 4 5 5 5
//  7   C 4 4 5 5 D
//  8   4 4 4 5 G 5
//  9   6 6 6
// 10   A 6 G
// 11   6 B 6