<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;
use Throwable;

class Day21 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $monkeys = $this->parseInput();
        return $this->getMonkeyValue($monkeys, Monkey::ROOT);
    }

    protected function solvePart2(): string
    {
        $monkeys = $this->parseInput();
        $monkeys[Monkey::HUMAN]->setHuman();
        $value = $this->getMonkeyValue($monkeys, $monkeys[Monkey::ROOT]->monkey1);
        if ($value === false) {
            $value = $this->getMonkeyValue($monkeys, $monkeys[Monkey::ROOT]->monkey2);
        }
        return $this->resolveHuman($monkeys, $value);
    }

    /**
     * @param Monkey[] $monkeys
     * @param string   $monkeyName
     * @return false|int
     */
    protected function getMonkeyValue(array $monkeys, string $monkeyName): false|int
    {
        $queue = [$monkeys[$monkeyName]];
        while ($queue) {
            $monkey = end($queue);
            if ($monkey->hasValue()) {
                array_pop($queue);
                break;
            }
            $complete = true;
            $m1 = $monkeys[$monkey->monkey1];
            $m2 = $monkeys[$monkey->monkey2];
            $m1->parent = $monkey->name;
            $m2->parent = $monkey->name;
            // We encountered a human
            if ($m1->isHuman() || $m2->isHuman()) {
                return false;
            }
            if (!$m1->hasValue()) {
                $queue[] = $m1;
                $complete = false;
            }
            if (!$m2->hasValue()) {
                $queue[] = $m2;
                $complete = false;
            }
            if ($complete) {
                $monkey->calculateValue($m1->value, $m2->value);
                array_pop($queue);
            }
        }
        return $monkeys[$monkeyName]->value;
    }

    /**
     * @param Monkey[] $monkeys
     * @param int    $total
     * @return int
     */
    protected function resolveHuman(array $monkeys, int $total): int
    {
        // Create queue from human to root + 1 level
        $queueMonkey = $monkeys[Monkey::HUMAN];
        $queue = [];
        while ($queueMonkey->parent) {
            array_unshift($queue, $queueMonkey->name);
            $queueMonkey = $monkeys[$queueMonkey->parent];
        }
        array_unshift($queue, $queueMonkey->name);

        // Run from top to bottom (only resolve sides that do not include the human)
        $monkey = $monkeys[array_shift($queue)];
        while (!$monkey->isHuman()) {
            if ($monkey->monkey1 == $queue[0]) {
                $subMonkey = $monkey->monkey2;
                $humanOp = 1;
            } else {
                $subMonkey = $monkey->monkey1;
                $humanOp = 2;
            }
            $value = $this->getMonkeyValue($monkeys, $subMonkey);
            switch ($monkey->operation) {
                case '+':
                    // x = h + y  =>  x - y = h  (10 = [6] + 4 => 10 - 4 = 6)
                    // x = y + h  =>  x - y = h  (10 = 4 + [6] => 10 - 4 = 6)
                    $total -= $value;
                    break;
                case '-':
                    if ($humanOp == 1) {
                        // x = h - y  =>  x + y = h  (4 = [10] - 6 => 4 + 6 = 10)
                        $total += $value;
                    } else {
                        // x = y - h  =>  y - x = h  (4 = 10 - [6] => 10 - 4 = 6)
                        $total = $value - $total;
                    }
                    break;
                case '*':
                    // x = y * h  =>  x / y = h  (10 = 2 * [5] => 10 / 2 = 5)
                    // x = h * y  =>  x / y = h  (10 = [2] * 5 => 10 / 5 = 2)
                    $total /= $value;
                    break;
                case '/':
                    if ($humanOp == 1) {
                        // x = h / y  =>  x * y = h  (2 = [10] / 5 => 2 * 5 = 10)
                        $total *= $value;
                    } else {
                        // x = y / h  =>  y - x = h  (2 = 10 / [5] => 10 / 2 = 5)
                        $total = $value / $total;
                    }
                    break;
            }
            $monkey = $monkeys[array_shift($queue)];
        }
        return $total;
    }

    /**
     * @return array<string,Monkey>
     */
    protected function parseInput(): array
    {
        $yells = [];
        foreach ($this->getInputLines() as $line) {
            list($monkey, $value) = explode(': ', $line);
            if (is_numeric($value)) {
                $yells[$monkey] = new Monkey($monkey, $value);
            } else {
                list($m1, $op, $m2) = explode(' ', $value);
                $yells[$monkey] = new Monkey($monkey, null, $op, $m1, $m2);
            }
        }
        return $yells;
    }
}

class Monkey
{
    public const ROOT  = 'root';
    public const HUMAN = 'humn';

    protected bool $human = false;

    public function __construct(
        public readonly string $name,
        public ?int $value = null,
        public ?string $operation = null,
        public ?string $monkey1 = null,
        public ?string $monkey2 = null,
        public ?string $parent = null,
    ) {
    }

    public function setHuman(): void
    {
        $this->human = true;
        $this->value = null;
    }

    public function isHuman(): bool
    {
        return $this->human;
    }

    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    public function calculateValue(int $v1, int $v2): void
    {
        switch ($this->operation) {
            case '+':
                $this->value = $v1 + $v2;
                break;
            case '-':
                $this->value = $v1 - $v2;
                break;
            case '*':
                $this->value = $v1 * $v2;
                break;
            case '/':
                $this->value = $v1 / $v2;
                break;
        }
    }
}

// PART 2 SOLUTION
// ---------------
//
// Using example input
//
// 1. Solve until one side completed
//
//   pppw = sjmn
//   cczh / lfqf = drzm * dbpl
//   (sllz + lgvd) / 4 = (hmdt - zczc) * 5
//   (4 + (ljgn * ptdq)) / 4 = (32 - 2) * 5
//   (4 + (2 * (humn - dvpt))) / 4 = 30 * 5
//   (4 + (2 * (humn - 3))) / 4 = 150
//
// 2. Resolve human side till complete
//
//   (4 + (2 * (humn - 3))) / 4 = 150
//   4 + (2 * (humn - 3)) = 150 * 4
//   4 + (2 * (humn - 3)) = 600
//   2 * (humn - 3) = 600 - 4
//   2 * (humn - 3) = 596
//   humn - 3 = 596 / 2
//   humn - 3 = 298
//   humn = 298 + 3
//   humn = 301