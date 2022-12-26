<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day25 extends AbstractSolution
{
    private array $factors = [
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '-' => -1,
        '=' => -2,
    ];

    protected function solvePart1(): string
    {
        $total = 0;
        foreach ($this->getInputLines() as $snafu) {
            $total += $this->snafuToInt($snafu);
        }
        return $this->intToSnafu($total);
    }

    protected function solvePart2(): string
    {
        return ':(';
    }

    private function snafuToInt(string $snafu): int
    {
        $total = 0;
        $pow = strlen($snafu) - 1;
        for ($i = 0; $i < strlen($snafu); $i++, $pow--) {
            $total += pow(5, $pow) * $this->factors[$snafu[$i]];
        }
        return $total;
    }

    private function intToSnafu(int $int): string
    {
        $pow = 0;
        $snafu = '';
        while ($int) {
            $curr = pow(5, $pow);
            $next = pow(5, $pow + 1);
            $mod = mod($int, $next);
            foreach ($this->factors as $s => $factor) {
                if ($factor >= 0) {
                    if ($mod == $factor * $curr) {
                        $int -= $curr * $factor;
                        $snafu = $s . $snafu;
                        break;
                    }
                } else {
                    if ($next + $factor * $curr == $mod) {
                        $int += $next - $mod;
                        $snafu = $s . $snafu;
                        break;
                    }
                }
            }
            $pow++;
        }
        return $snafu;
    }
}
