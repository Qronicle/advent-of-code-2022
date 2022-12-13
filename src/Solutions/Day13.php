<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day13 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $pairs = array_map(fn (string $p) => array_map('json_decode', explode("\n", $p)), explode("\n\n", $this->rawInput));
        $indices = 0;
        foreach ($pairs as $i => $pair) {
            if ($this->compareArrays(...$pair) == -1) {
                $indices += $i + 1;
            }
        }
        return $indices;
    }

    protected function solvePart2(): string
    {
        $packets = array_filter(array_map(fn (string $p) => $p ? json_decode($p) : null, explode("\n", $this->rawInput)), fn ($val) => isset($val));
        $packets[] = [[2]];
        $packets[] = [[6]];
        usort($packets, [$this, 'compareArrays']);
        $total = 1;
        foreach ($packets as $i => $packet) {
            $json = json_encode($packet);
            if ($json == '[[2]]' || $json == '[[6]]') {
                $total *= ($i + 1);
            }
        }
        return $total;
    }

    protected function compareArrays(array $a, array $b, int $depth = 1): int
    {
        foreach ($a as $i => $aVal) {
            if (!isset($b[$i])) {
                return 1;
            }
            $bVal = $b[$i];
            $aArr = is_array($aVal);
            $bArr = is_array($bVal);
            if ($aArr || $bArr) {
                $aVal = $aArr ? $aVal : [$aVal];
                $bVal = $bArr ? $bVal : [$bVal];
                if (($result = $this->compareArrays($aVal, $bVal, $depth+1)) != 0) {
                    return $result;
                }
            } else {
                if ($aVal > $bVal) {
                    return 1;
                } elseif ($aVal < $bVal) {
                    return -1;
                }
            }
        }
        return count($a) <=> count($b);
    }
}
