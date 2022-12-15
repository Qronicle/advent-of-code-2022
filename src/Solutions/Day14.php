<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\ImageOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day14 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->run(new CaveSystem(500, 0));
    }

    protected function solvePart2(): string
    {
        return $this->run(new FlooredCaveSystem(500, 0));
    }

    protected function run(CaveSystem $caveSystem, int $render = 0): int
    {
        foreach ($this->getInputLines() as $scan) {
            $path = array_map(fn(string $coords) => explode(',', $coords), explode(' -> ', $scan));
            $caveSystem->drawPath($path);
        }
        $caveSystem->finalize();
        if ($render) {
            $bounds = $caveSystem
                ->simulate()
                ->getBounds();
            $caveSystem
                ->reset()
                ->enableRendering($render, $bounds);
        }
        return $caveSystem
            ->simulate()
            ->getSandCount();
    }
}
class CaveSystem
{
    const ROCK   = '#';
    const AIR    = '.';
    const SAND   = 'o';
    const VOID   = 0;

    /** @var array<int,array<int, string>> */
    protected array $map = [];

    protected int $xBoundL;
    protected int $xBoundR;
    protected int $yBoundB;
    protected int $sandCount = 0;

    protected bool $renderingEnabled = false;
    protected string $renderDir;
    protected array $renderBounds;

    public function __construct(
        protected int $springX,
        protected int $springY,
    ) {
        $this->xBoundL = $this->springX;
        $this->xBoundR = $this->springX;
        $this->yBoundB = $this->springY;
    }

    public function simulate(): self
    {
        $currX = $this->springX;
        $currY = $this->springY;
        $dirs = [[0, 1], [-1, 1], [1, 1]];
        while (true) {
            $fell = false;
            foreach ($dirs as $dir) {
                $nextX = $currX + $dir[0];
                $nextY = $currY + $dir[1];
                $material = $this->getMaterialAt($nextX, $nextY);
                if ($material == self::AIR) {
                    $currX = $nextX;
                    $currY = $nextY;
                    $fell = true;
                    break;
                } elseif ($material == self::VOID) {
                    $this->renderAnimation();
                    break 2;
                }
            }
            if (!$fell) {
                $this->sandCount++;
                $this->map[$currY][$currX] = self::SAND;
                $this->renderImage($this->sandCount);
                // we reached the spring
                if ($currY == $this->springY) {
                    $this->renderAnimation();
                    break;
                }
                $currX = $this->springX;
                $currY = $this->springY;
            }
        }

        return $this;
    }

    protected function getMaterialAt(int $x, $y): string|int
    {
        return $this->map[$y][$x];
    }

    public function drawPath(array $path): self
    {
        $prev = null;
        foreach ($path as $coords) {
            $this->xBoundL = min($this->xBoundL, $coords[0]);
            $this->xBoundR = max($this->xBoundR, $coords[0]);
            $this->yBoundB = max($this->yBoundB, $coords[1]);
            if ($prev) {
                foreach (range($coords[1], $prev[1]) as $y) {
                    foreach (range($coords[0], $prev[0]) as $x) {
                        $this->map[$y][$x] = self::ROCK;
                    }
                }
            }
            $prev = $coords;
        }
        return $this;
    }

    public function finalize(): self
    {
        for ($y = 0; $y <= $this->yBoundB; $y++) {
            for ($x = $this->xBoundL; $x <= $this->xBoundR; $x++) {
                $this->map[$y][$x] = $this->map[$y][$x] ?? self::AIR;
            }
            $this->map[$y][$this->xBoundL - 1] = self::VOID;
            $this->map[$y][$this->xBoundR + 1] = self::VOID;
            ksort($this->map[$y]);
        }
        $this->map[$this->yBoundB + 1] = array_fill($this->xBoundL - 1, count($this->map[0]), self::VOID);
        ksort($this->map);
        return $this;
    }

    public function drawToScreen(): self
    {
        for ($y = 0; $y <= $this->yBoundB; $y++) {
            for ($x = $this->xBoundL; $x <= $this->xBoundR; $x++) {
                echo $this->map[$y][$x] ?? self::AIR;
            }
            echo "\n";
        }
        echo "\n";
        return $this;
    }

    public function renderImage(int $step): self
    {
        if (!$this->renderingEnabled) {
            return $this;
        }
        $imgData = '';
        for ($y = $this->renderBounds['t']; $y <= $this->renderBounds['b']; $y++) {
            for ($x = $this->renderBounds['l']; $x <= $this->renderBounds['r']; $x++) {
                $imgData .= $this->map[$y][$x] ?? self::AIR;
            }
            if ($y < $this->renderBounds['b']) {
                $imgData .= "\n";
            }
        }
        $colors = [
            self::ROCK => [60,60,60],
            self::AIR  => [117,204,223],
            self::SAND => [206,117,49],
            self::VOID => [0,0,0],
        ];
        $filename = $this->renderDir . str_pad($step, 10, '0', STR_PAD_LEFT) . '.png';
        ImageOutput::strtoimg($imgData, $filename, 4, $colors);
        return $this;
    }

    public function renderAnimation(): self
    {
        if ($this->renderingEnabled) {
            ImageOutput::pngSequenceToGif($this->renderDir, '/aoc-13.gif');
        }
        return $this;
    }

    public function getBounds(): array
    {
        return [
            'l' => $this->xBoundL,
            'r' => $this->xBoundR,
            't' => 0,
            'b' => $this->yBoundB,
        ];
    }

    public function reset(): self
    {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $val) {
                if ($val == self::SAND) {
                    $this->map[$y][$x] = self::AIR;
                }
            }
        }
        $this->sandCount = 0;
        return $this;
    }

    public function enableRendering(int $part, array $bounds): self
    {
        $this->renderingEnabled = true;
        $this->renderDir = 'var/out/13/' . $part . '/';
        $this->renderBounds = $bounds;
        return $this;
    }

    public function getSandCount(): int
    {
        return $this->sandCount;
    }
}

class FlooredCaveSystem extends CaveSystem
{
    protected int $floorY;

    protected function getMaterialAt(int $x, $y): string|int
    {
        if ($y == $this->floorY) {
            return self::ROCK;
        }
        return $this->map[$y][$x] ?? self::AIR;
    }

    public function finalize(): self
    {
        $this->floorY = $this->yBoundB + 2;
        return $this;
    }

    public function getBounds(): array
    {
        return [
            'l' => min(array_keys($this->map[$this->yBoundB + 1])),
            'r' => max(array_keys($this->map[$this->yBoundB + 1])),
            't' => 0,
            'b' => $this->yBoundB + 1,
        ];
    }
}