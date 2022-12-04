<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day03 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $totalPriorities = 0;
        foreach ($this->getInputLines() as $inputLine) {
            $compartment1 = str_split($inputLine);
            $compartmentSize = count($compartment1) >> 1;
            $compartment2 = array_splice($compartment1, $compartmentSize, $compartmentSize);
            $doubleItem = array_first(array_intersect($compartment1, $compartment2));
            $totalPriorities += $this->getItemPriority($doubleItem);
        }
        return $totalPriorities;
    }

    protected function solvePart2(): string
    {
        $totalPriorities = 0;
        $rucksacks = $this->getInputLines();
        $numRucksacks = count($this->getInputLines());
        for ($i = 0; $i < $numRucksacks; $i += 3) {
            $badgeItem = array_first(array_intersect(
                str_split($rucksacks[$i]),
                str_split($rucksacks[$i + 1]),
                str_split($rucksacks[$i + 2])
            ));
            $totalPriorities += $this->getItemPriority($badgeItem);
        }
        return $totalPriorities;
    }

    protected function getItemPriority(string $item): int
    {
        $ord = ord($item);
        return $ord > 90 ? $ord - 96 : $ord - 38;
    }
}
