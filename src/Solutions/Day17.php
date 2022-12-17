<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\TextOutput;
use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;

class Day17 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return (new Tetris(2022))->run(str_split($this->rawInput));
    }

    protected function solvePart2(): string
    {
        return (new Tetris(1000000000000))->run(str_split($this->rawInput));
    }
}

class Tetris
{
    public const MOVE_LEFT  = '<';
    public const MOVE_RIGHT = '>';

    protected array $lineStates = [];

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

    protected int $inputIndex = -1;

    protected array $map;

    public function __construct(
        protected int $maxShapes,
    ) {
        $this->shapes = array_map(fn(array $shape) => new Shape(...$shape), $this->shapes);
        $this->map = [0 => []];
    }

    public function run(array $input): int
    {
        $numInputs = count($input);
        try {
            $this->spawnBlock();
            while (true) {
                $this->inputIndex = ++$this->inputIndex % $numInputs;
                $this->move($input[$this->inputIndex]);
                try {
                    $this->fall();
                } catch (RepetitionException $ex) {
                    $numShapesPerRepetition = $this->numShapes - $ex->firstNumShapes;
                    $heightPerRepetition = $this->bottom - $ex->firstBottom;
                    $numRepetitions = floor(($this->maxShapes - $ex->firstNumShapes) / $numShapesPerRepetition);
                    $remainingTurns = ($this->maxShapes - $ex->firstNumShapes) % $numShapesPerRepetition;
                    // Update internal counters
                    $this->maxShapes = $this->numShapes + $remainingTurns;
                    $this->bottom = 0;
                    $this->lineStates = [];
                    return $ex->firstBottom + ($numRepetitions * $heightPerRepetition) + $this->run($input);
                }
            }
        } catch (ShapeLimitExceededException) {
            return $this->getHeight();
        }
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
        if ($this->shapePos[1] == 0) {
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
        // Check for solid line
        for ($y = $this->shapePos[1] + $this->currentShape->height - 1; $y >= $this->shapePos[1]; $y--) {
            if (count($this->map[$y]) == $this->width) {
                array_splice($this->map, 0, $y + 1);
                $this->bottom += $y + 1;
                $this->height -= $y + 1;
                $state = json_encode([
                    'shape' => $this->numShapes % 5,
                    'input' => $this->inputIndex,
                    'map'   => $this->map
                ]);
                if (isset($this->lineStates[$state])) {
                    throw new RepetitionException($this, ...$this->lineStates[$state]);
                }
                $this->lineStates[$state] = [
                    'firstBottom'    => $this->bottom,
                    'firstNumShapes' => $this->numShapes,
                ];
                break;
            }
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
        $top = max($this->height + 1, $this->currentShape ? $this->currentShape->height + $this->shapePos[1] + 1 : 0);
        $extra = null;
        if ($this->currentShape) {
            $extra = ['@' => ['coords' => $this->currentShape->allBlocks, 'offset' => $this->shapePos]];
        }
        echo TextOutput::incompleteMap($this->map, ['h' => $top, 'w' => $this->width], '.', $extra, true);
    }

    public function getHeight(): int
    {
        return $this->bottom + $this->height;
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

class RepetitionException extends Exception
{
    public function __construct(
        public readonly Tetris $tetris,
        public readonly int $firstBottom,
        public readonly int $firstNumShapes,
    ) {
        parent::__construct();
    }
}