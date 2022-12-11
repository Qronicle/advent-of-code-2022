<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day11 extends AbstractSolution
{
    /** @var Monkey[] */
    protected array $monkeys;

    protected function solvePart1(): string
    {
        $this->initMonkeys(Monkey::class);
        return $this->run(20);
    }

    protected function solvePart2(): string
    {
        $this->initMonkeys(AngryMonkey::class);
        return $this->run(10000);
    }

    protected function run(int $rounds): int
    {
        // Play the rounds
        for ($i = 0; $i < $rounds; $i++) {
            foreach ($this->monkeys as $monkey) {
                $monkey->throwAll();
            }
        }
        // Sort on numInspected
        usort($this->monkeys, fn (Monkey $a, Monkey $b) => $b->numInspected <=> $a->numInspected);
        return $this->monkeys[0]->numInspected * $this->monkeys[1]->numInspected;
    }

    protected function initMonkeys(string $monkeyType): void
    {
        $definitions = explode("\n\n", $this->rawInput);
        $this->monkeys = array_map(fn () => new $monkeyType(), array_fill(0, count($definitions), null));
        foreach ($definitions as $i => $definition) {
            $this->monkeys[$i]->init($definition, $this->monkeys);
        }
        $lcm = null;
        foreach ($this->monkeys as $monkey) {
            $lcm = $lcm ? least_common_multiple($monkey->throwCondition, $lcm) : $monkey->throwCondition;
        }
        foreach ($this->monkeys as $monkey) {
            $monkey->lcm = $lcm;
        }
    }
}

class Monkey
{
    public array $items;
    public string $operation;
    public int $throwCondition;
    public array $throwTargets;
    public int $lcm;

    public int $numInspected = 0;

    public function init(string $definition, array $monkeys): void
    {
        $d = explode("\n", $definition);
        $this->items = explode(', ', array_last(explode(': ', $d[1])));
        $this->operation = 'return ' . str_replace('old', '$item', substr($d[2], 19)) . ';';
        $this->throwCondition = array_last(explode(' divisible by ', $d[3]));
        $this->throwTargets = [
            $monkeys[(int)array_last(explode(' monkey ', $d[5]))],
            $monkeys[(int)array_last(explode(' monkey ', $d[4]))],
        ];
    }

    public function throwAll(): void
    {
        foreach ($this->items as $item) {
            $item = floor(eval($this->operation) / 3);
            $target = $this->throwTargets[$item % $this->throwCondition == 0 ? 1 : 0];
            $target->items[] = $item;
            $this->numInspected++;
        }
        $this->items = [];
    }
}

class AngryMonkey extends Monkey
{
    public function throwAll(): void
    {
        foreach ($this->items as $item) {
            $item = eval($this->operation) % $this->lcm;
            $target = $this->throwTargets[$item % $this->throwCondition == 0 ? 1 : 0];
            $target->items[] = $item;
            $this->numInspected++;
        }
        $this->items = [];
    }
}