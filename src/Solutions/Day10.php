<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\TextOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day10 extends AbstractSolution
{
    protected int $cycle = 0;
    protected int $x = 1;
    protected int $signalStrength = 0;
    protected array $screen;

    protected function solvePart1(): string
    {
        $this->run(function() {
            if ((++$this->cycle - 20) % 40 == 0) {
                $this->signalStrength += $this->cycle * $this->x;
                echo "Cycle {$this->cycle}: {$this->x}\n";
            }
        });
        return $this->signalStrength;
    }

    protected function solvePart2(): string
    {
        $this->run(function(){
            $hor = $this->cycle % 40;
            $this->screen[floor($this->cycle / 40)][$hor] = $hor >= $this->x -1 && $hor <= $this->x +1 ? '#' : ' ';
            $this->cycle++;
        });
        echo TextOutput::map2d($this->screen);
        return '^ this';
    }

    protected function run(callable $increaseCycle): void
    {
        foreach ($this->getInputLines() as $instruction) {
            $increaseCycle();
            if ($instruction == 'noop') {
                continue;
            }
            $add = array_last(explode(' ', $instruction));
            $increaseCycle();
            $this->x += $add;
        }
    }
}
