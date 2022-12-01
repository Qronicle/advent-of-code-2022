<?php

namespace AdventOfCode\Common\Solution;

/**
 * Class AbstractSolution
 *
 * @package AdventOfCode\Common\Solution
 * @author  Ruud Seberechts
 */
abstract class AbstractSolution
{
    protected string $rawInput;

    public function solve(int $part, string $inputFilename): string
    {
        $method = 'solvePart' . $part;
        $this->rawInput = file_get_contents($inputFilename);
        return $this->$method();
    }

    abstract protected function solvePart1(): string;

    abstract protected function solvePart2(): string;

    protected function getInputLines(): array
    {
        return explode("\n", $this->rawInput);
    }
}
