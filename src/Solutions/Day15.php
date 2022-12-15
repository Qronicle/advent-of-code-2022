<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day15 extends AbstractSolution
{
    const BEACON  = 'B';
    const SENSOR  = 'S';
    const COVERED = '#';
    const UNKNOWN = '.';

    protected function solvePart1(): string
    {
        $testRow = $this->aocInputType == 'test' ? 10 : 2000000;
        $regex = '/Sensor at x=([\-0-9]+), y=([\-0-9]+): closest beacon is at x=([\-0-9]+), y=([\-0-9]+)/i';
        $sensor = new Vector2();
        $beacon = new Vector2();
        $rowObjects = [];
        $rowCoveredRanges = [];
        foreach ($this->getInputLines() as $report) {
            preg_match($regex, $report, $matches);
            $sensor->set($matches[1], $matches[2]);
            $beacon->set($matches[3], $matches[4]);
            if ($sensor->y == $testRow) {
                $rowObjects[$sensor->x] = self::SENSOR;
            }
            if ($beacon->y == $testRow) {
                $rowObjects[$beacon->x] = self::BEACON;
            }
            $distFromTestRow = abs($sensor->y - $testRow);
            $distToBeacon = $sensor->manhattanDistanceTo($beacon);
            if ($distFromTestRow <= $distToBeacon) {
                $over = $distToBeacon - $distFromTestRow;
                $range = [$sensor->x - $over, $sensor->x + $over];
                $this->addRange($rowCoveredRanges, $range);
            }
        }
        foreach ($rowObjects as $x => $type) {
            $this->removeFromRanges($rowCoveredRanges, $x);
        }
        return $this->sumRanges($rowCoveredRanges);
    }

    protected function solvePart2(): string
    {
        ini_set('memory_limit', '4G');
        $testRange = $this->aocInputType == 'test' ? 20 : 4000000;
        $regex = '/Sensor at x=([\-0-9]+), y=([\-0-9]+): closest beacon is at x=([\-0-9]+), y=([\-0-9]+)/i';
        $sensor = new Vector2();
        $beacon = new Vector2();
        $rows = array_fill(0, $testRange + 1, []);
        foreach ($this->getInputLines() as $report) {
            preg_match($regex, $report, $matches);
            $sensor->set($matches[1], $matches[2]);
            $beacon->set($matches[3], $matches[4]);
            $distToBeacon = $sensor->manhattanDistanceTo($beacon);
            // find y range
            $min = max(0, $sensor->y - $distToBeacon - 1);
            $max = min($testRange, $sensor->y + $distToBeacon + 1);
            for ($y = $min; $y <= $max; $y++) {
                $distFromSensor = abs($sensor->y - $y);
                $over = $distToBeacon - $distFromSensor;
                $range = [$sensor->x - $over, $sensor->x + $over];
                $this->addRange($rows[$y], $range);
            }
        }
        foreach ($rows as $y => $ranges) {
            $rangesInRange = array_filter($ranges, fn (array $range) => $range[0] <= $testRange && $range[1] >= 0);
            if (count($rangesInRange) > 1) {
                return $y + (($rangesInRange[0][1] + 1) * 4000000);
            }
        }
        return "ok";
    }

    protected function addRange(array &$ranges, array $range): void
    {
        if (!$ranges) {
            $ranges[] = $range;
            return;
        }

        // Insert range at correct position
        $inserted = false;
        foreach ($ranges as $i => $r) {
            if ($range[0] < $r[0]) {
                // insert before
                array_splice($ranges, $i, 0, [$range]);
                $inserted = true;
                break;
            }
            if ($range[0] == $r[0]) {
                $ranges[$i][1] = max($r[1], $range[1]);
                $inserted = true;
                break;
            }
        }
        if (!$inserted) {
            $ranges[] = $range;
        }

        // Merge overlapping ranges
        for ($i = 0; $i < count($ranges) - 1; $i++) {
            // completely before next
            if ($ranges[$i][1] < $ranges[$i + 1][0] - 1) {
                continue;
            }
            // ends within next => extend current and remove next
            if ($ranges[$i][1] < $ranges[$i + 1][1]) {
                $ranges[$i][1] = $ranges[$i + 1][1];
                array_splice($ranges, $i + 1, 1);
                $i--;
                continue;
            }
            // ends after next => remove next and loop again
            array_splice($ranges, $i + 1, 1);
            $i--;
        }
    }

    protected function removeFromRanges(array &$ranges, int $num): void
    {
        for ($i = 0; $i < count($ranges); $i++) {
            // split range
            if ($num > $ranges[$i][0] && $num < $ranges[$i][1]) {
                array_splice($ranges, $i + 1, 0, [[$num + 1, $ranges[$i][1]]]);
                $ranges[$i][1] = $num - 1;
                break;
            }
            if ($num == $ranges[$i][0]) {
                // Remove range if it's only that number
                if ($ranges[$i][1] == $num) {
                    array_splice($ranges, $i, 1);
                } else {
                    $ranges[$i][0]++;
                }
                break;
            }
            if ($num == $ranges[$i][1]) {
                $ranges[$i][1]--;
                break;
            }
        }
    }

    protected function sumRanges(array $ranges): int
    {
        $total = 0;
        foreach ($ranges as $range) {
            $total += ($range[1] - $range[0]) + 1;
        }
        return $total;
    }
}

class Vector2
{
    public function __construct(
        public int $x = 0,
        public int $y = 0,
    ) {
    }

    public function set(int $x = 0, int $y = 0): self
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    public function add(Vector2 $v2): self
    {
        $this->x += $v2->x;
        $this->y += $v2->y;
        return $this;
    }

    public function manhattanDistanceTo(Vector2 $v2): int
    {
        return abs($this->x - $v2->x) + abs($this->y - $v2->y);
    }
}