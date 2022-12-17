<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\TextOutput;
use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;

class Day17 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $inputs = str_split($this->rawInput);
        $numInputs = count($inputs);
        $inputIndex = 0;
        $tetris = new Tetris(2022);
        try {
            while (true) {
                $tetris->move($inputs[$inputIndex++ % $numInputs]);
                $tetris->fall();
            }
        } catch (ShapeLimitExceededException) {
            return $tetris->getHeight();
        }
    }

    protected function solvePart2(): string
    {
        //
    }
}

class Tetris
{
    public const MOVE_LEFT  = '<';
    public const MOVE_RIGHT = '>';

    protected array $shapes = [
        0 => [
            'blocks' => [[0, 0], [1, 0], [2, 0], [3, 0]],
            'width'  => 4,
            'height' => 1,
        ],
        1 => [
            'blocks' => [[1, 0], [0, 1], [2, 1], [1, 2]],
            'width'  => 3,
            'height' => 3,
            'extra'  => [[1, 1]], // middle block we don't need to check
        ],
        2 => [
            'blocks' => [[0, 0], [1, 0], [2, 0], [2, 1], [2, 2]],
            'width'  => 3,
            'height' => 3,
        ],
        3 => [
            'blocks' => [[0, 0], [0, 1], [0, 2], [0, 3]],
            'width'  => 1,
            'height' => 4,
        ],
        4 => [
            'blocks' => [[0, 0], [1, 0], [0, 1], [1, 1]],
            'width'  => 2,
            'height' => 2,
        ],
    ];

    protected int $width = 7;
    protected int $bottom = 0;
    protected int $height = 0;
    protected int $numShapes = 0;
    protected ?Shape $currentShape = null;
    protected array $shapePos;

    protected array $map;

    public function __construct(
        protected readonly ?int $maxShapes = null,
    ) {
        $this->shapes = array_map(fn(array $shape) => new Shape(...$shape), $this->shapes);
        $this->map = [0 => []];
        $this->spawnBlock();
    }

    public function move(string $move): void
    {
        if ($move == self::MOVE_LEFT) {
            if ($this->shapePos[0] == 0) {
                return;
            }
            $x = -1;
        } else {
            if ($this->shapePos[0] + $this->currentShape->width == $this->width) {
                return;
            }
            $x = 1;
        }
        foreach ($this->currentShape->blocks as $coords) {
            if ($this->map[$coords[1] + $this->shapePos[1]][$coords[0] + $this->shapePos[0] + $x] ?? false) {
                return;
            }
        }
        $this->shapePos[0] += $x;
    }

    public function fall(): bool
    {
        if ($this->shapePos[1] == $this->bottom) {
            $this->freezeCurrentShape();
            return false;
        }
        foreach ($this->currentShape->blocks as $coords) {
            if ($this->map[$coords[1] + $this->shapePos[1] - 1][$coords[0] + $this->shapePos[0]] ?? false) {
                $this->freezeCurrentShape();
                return false;
            }
        }
        $this->shapePos[1]--;
        return true;
    }

    public function freezeCurrentShape(): void
    {
        foreach ($this->currentShape->allBlocks as $coords) {
            $this->map[$coords[1] + $this->shapePos[1]][$coords[0] + $this->shapePos[0]] = true;
            $this->height = max($this->height, $this->shapePos[1] + $this->currentShape->height);
        }
        $this->spawnBlock();
    }

    protected function spawnBlock(): void
    {
        $shapeIndex = $this->numShapes++ % 5;
        if ($this->maxShapes && $this->numShapes > $this->maxShapes) {
            $this->currentShape = null;
            throw new ShapeLimitExceededException();
        }
        $this->shapePos = [2, $this->height + 3];
        $this->currentShape = $this->shapes[$shapeIndex];
    }

    public function renderToScreen(): void
    {
        $top = $this->currentShape ? ($this->currentShape->height + $this->shapePos[1] + 1) : ($this->height + 1);
        dump('');
        $map = array_reverse(
            array_fill($this->bottom, $top - $this->bottom, array_fill(0, $this->width, '.')),
            true
        );
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tmp) {
                $map[$y][$x] = '#';
            }
        }
        if ($this->currentShape) {
            foreach ($this->currentShape->allBlocks as $coords) {
                $map[$coords[1] + $this->shapePos[1]][$coords[0] + $this->shapePos[0]] = '@';
            }
        }
        $map[] = array_fill(0, $this->width, "=");
        echo TextOutput::map2d($map);
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}

class Shape
{
    public array $allBlocks;

    public function __construct(
        public readonly array $blocks,
        public readonly int $width,
        public readonly int $height,
        public readonly array $extra = [],
    ) {
        $this->allBlocks = [...$this->blocks, ...$this->extra];
    }
}

class ShapeLimitExceededException extends Exception
{
}