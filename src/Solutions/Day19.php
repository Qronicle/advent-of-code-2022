<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day19 extends AbstractSolution
{
    public const ORE      = 0;
    public const CLAY     = 1;
    public const OBSIDIAN = 2;
    public const GEODE    = 3;

    protected function solvePart1(): string
    {
        $blueprints = $this->getBlueprints();
        $qualityLevel = 0;
        foreach ($blueprints as $id => $blueprint) {
            echo "Testing blueprint $id     ";
            $qualityLevel += $id * $this->runBlueprint($blueprint, 24);
            echo "\n => $qualityLevel\n";
        }
        return $qualityLevel;
    }

    protected function solvePart2(): string
    {
        $blueprints = array_slice($this->getBlueprints(), 0, 3);
        $total = 1;
        foreach ($blueprints as $id => $blueprint) {
            echo "Testing blueprint $id     ";
            $total *= $this->runBlueprint($blueprint, 32);
            echo "\n => $total\n";
        }
        return $total;
    }

    public function runBlueprint(array $blueprint, int $time): int
    {
        // time remaining, robots, resources
        $situation = [[1, 0, 0, 0], [0, 0, 0, 0]];
        $ores = [0, 1, 2, 3];
        $maxRobots = [0, 0, 0, PHP_INT_MAX];
        foreach ($blueprint as $robotType => $neededResources) {
            foreach ($neededResources as $ore => $amount) {
                $maxRobots[$ore] = max($amount, $maxRobots[$ore]);
            }
        }
        $permutations = [json_encode($situation) => true];
        $maxGeodes = 0;
        while ($time-- > 0) {
            if ($time < 11) {
                echo "$time ";
            }
            $newPermutations = [];
            foreach ($permutations as $permutation => $tmp) {
                list($robots, $resources) = json_decode($permutation);
                if ($maxGeodes - $resources[self::GEODE] > 20) {
                    continue;
                }
                $numRobotPermutationsCreated = 0;
                // create all possible robots
                foreach ($ores as $ore) {
                    $newResources = $resources;
                    $newRobots = $robots;
                    foreach ($blueprint[$ore] as $resourceOre => $resourceNeeded) {
                        $newResources[$resourceOre] -= $resourceNeeded;
                        if ($newResources[$resourceOre] < 0) {
                            continue 2;
                        }
                    }
                    $newRobots[$ore]++;
                    $numRobotPermutationsCreated++;
                    // check whether it makes sense to build this robot
                    if ($newRobots[$ore] > $maxRobots[$ore]) {
                        continue;
                    }
                    // gather resources
                    array_walk($newResources, fn(int &$amount, int $ore) => $amount += $robots[$ore]);
                    $newPermutations[json_encode([$newRobots, $newResources])] = $newResources[self::GEODE];
                    $maxGeodes = max($maxGeodes, $newResources[self::GEODE]);
                }
                // Only add resource gathering only step if not all robots could be created
                if ($numRobotPermutationsCreated < 4) {
                    // gather resources
                    array_walk($resources, fn(int &$amount, int $ore) => $amount += $robots[$ore]);
                    $newPermutations[json_encode([$robots, $resources])] = $resources[self::GEODE];
                    $maxGeodes = max($maxGeodes, $resources[self::GEODE]);
                }
            }
            $permutations = $newPermutations;
        }
        return $permutations ? max($permutations) : 0;
    }

    protected function getBlueprints(): array
    {
        ini_set('memory_limit', '32G');
        $regex = '/Blueprint ([0-9]+): Each ore robot costs ([0-9]+) ore. ' .
            'Each clay robot costs ([0-9]+) ore. ' .
            'Each obsidian robot costs ([0-9]+) ore and ([0-9]+) clay. ' .
            'Each geode robot costs ([0-9]+) ore and ([0-9]+) obsidian./';
        $blueprints = [];
        foreach ($this->getInputLines() as $bp) {
            preg_match($regex, $bp, $matches);
            $blueprints[$matches[1]] = [
                self::ORE      => [self::ORE => $matches[2]],
                self::CLAY     => [self::ORE => $matches[3]],
                self::OBSIDIAN => [self::ORE => $matches[4], self::CLAY => $matches[5]],
                self::GEODE    => [self::ORE => $matches[6], self::OBSIDIAN => $matches[7]],
            ];
        }
        return $blueprints;
    }
}
