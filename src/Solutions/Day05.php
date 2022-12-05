<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day05 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->run(new CrateMover9000());
    }

    protected function solvePart2(): string
    {
        return $this->run(new CrateMover9001());
    }

    protected function run(AbstractCrateMover $crateMover): string
    {
        return $crateMover->run($this->rawInput);
    }
}

abstract class AbstractCrateMover
{
    public function run(string $input): string
    {
        list($stacks, $instructions) = $this->parseInput($input);
        foreach ($instructions as $instruction) {
            $slice = array_splice($stacks[$instruction[1] - 1], 0, $instruction[0]);
            $this->moveCrates($slice, $stacks[$instruction[2] - 1]);
        }
        return implode('', array_map(fn(array $stack) => $stack[0], $stacks));
    }

    protected function parseInput(string $input): array
    {
        list($boxes, $instructions) = array_map(fn(string $a) => explode("\n", $a), explode("\n\n", $input));
        $xAxis = array_pop($boxes);
        $numStacks = (int)array_last(explode(' ', $xAxis));
        $stacks = array_fill(0, $numStacks, []);
        foreach ($boxes as $line) {
            for ($i = 0; $i < $numStacks; $i++) {
                if ($box = trim(($line[1 + ($i * 4)] ?? false))) {
                    $stacks[$i][] = $box;
                }
            }
        }
        $instructions = array_map(function (string $instruction): array {
            $words = explode(' ', $instruction);
            return [$words[1], $words[3], $words[5]];
        }, $instructions);
        return [
            $stacks,
            $instructions,
        ];
    }

    abstract function moveCrates(array $crates, array &$stack): void;
}

class CrateMover9000 extends AbstractCrateMover
{
    function moveCrates(array $crates, array &$stack): void
    {
        array_unshift($stack, ...array_reverse($crates));
    }
}

class CrateMover9001 extends AbstractCrateMover
{
    function moveCrates(array $crates, array &$stack): void
    {
        array_unshift($stack, ...$crates);
    }
}