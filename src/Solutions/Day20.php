<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day20 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->run();
    }

    protected function solvePart2(): string
    {
        return $this->run(811589153, 10);
    }

    protected function run(int $key = 1, int $mixes = 1): int
    {
        $originalList = $this->getInputLines();
        $count = count($originalList);
        $zero = null;
        for ($index = 0; $index < $count; $index++) {
            $originalList[$index] = new LinkedItem($index, $originalList[$index] * $key);
            if ($originalList[$index]->value == 0) {
                $zero = $originalList[$index];
            }
        }
        $indexedList = $originalList;
        for ($mix = 0; $mix < $mixes; $mix++) {
            foreach ($originalList as $item) {
                array_splice($indexedList, $item->index, 1);
                $newIndex = ($item->index + $item->value) % ($count - 1);
                array_splice($indexedList, $newIndex, 0, [$item]);
                foreach ($indexedList as $i => $it) {
                    $it->index = $i;
                }
            }
        }
        $total = 0;
        foreach ([1000, 2000, 3000] as $grove) {
            $total += $indexedList[($grove + $zero->index) % ($count)]->value;
        }
        return $total;
    }
}

class LinkedItem
{
    public function __construct(
        public int $index,
        public readonly int $value,
    ) {
    }
}
