<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day16 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $valves = $this->parseInput();
        $unopenedValves = $this->getPressuredValves($valves);
        return $this->calcMaxPressure($valves, new Route($unopenedValves));
    }

    protected function solvePart2(): string
    {
        $valves = $this->parseInput();
        $unopenedValves = array_keys($this->getPressuredValves($valves));
        $groups = [];
        $this->divideGroups([array_splice($unopenedValves, 0, 1), []], $unopenedValves, $groups);
        $max = 0;
        foreach ($groups as $i => list($santaValves, $elephantValves)) {
            $max = max($max,
                $this->calcMaxPressure($valves, new Route(array_flip($santaValves), 26))
                + $this->calcMaxPressure($valves, new Route(array_flip($elephantValves), 26))
            );
        }
        return $max;
    }

    protected function calcMaxPressure(array $valves, Route $route): int
    {
        $routes = [$route];
        $max = 0;
        while ($routes) {
            $newRoutes = [];
            foreach ($routes as $route) {
                // Open all unopened valves
                foreach ($route->unopenedValves as $valve => $tmp) {
                    $newRoute = (clone $route);
                    if ($newRoute->moveAndOpenValve($valves[$valve])) {
                        $newRoutes[] = $newRoute;
                        $max = max($max, $newRoute->totalPressure);
                    }
                }
            }
            $routes = $newRoutes;
        }
        return $max;
    }

    protected function divideGroups(array $groups, array $pool, array &$result): void
    {
        $next = array_shift($pool);
        foreach ($groups as $g => $group) {
            $group[] = $next;
            $divideGroups = $groups;
            $divideGroups[$g] = $group;
            if ($pool) {
                $this->divideGroups($divideGroups, $pool, $result);
            } elseif (count($divideGroups[0]) > 4 && count($divideGroups[1]) > 4) {
                // we assume that we are okay with at least 4 valves per person to reduce runtime
                $result[] = $divideGroups;
            }
        }
    }

    protected function getPressuredValves(array $valves): array
    {
        return array_filter(array_map(fn() => true, $valves), fn ($k) => $valves[$k]->rate > 0, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string,Valve> $valves
     * @return array
     */
    protected function calculateOptimalRoutesBetweenValves(array $valves): array
    {
        $optimalRoutes = [];
        foreach ($valves as $valve) {
            $targets = [];
            $routes = array_map(fn(string $v) => [$v], $valve->connections);
            while ($routes) {
                $newRoutes = [];
                foreach ($routes as $route) {
                    $tValve = array_last($route);
                    $targets[$tValve] = count($route);
                    foreach ($valves[$tValve]->connections as $connection) {
                        if (isset($targets[$connection]) || $connection == $valve->name) {
                            continue;
                        }
                        $newRoute = $route;
                        $newRoute[] = $connection;
                        $newRoutes[] = $newRoute;
                    }
                }
                $routes = $newRoutes;
            }
            $optimalRoutes[$valve->name] = $targets;
        }
        return $optimalRoutes;
    }

    /**
     * @return array<string,Valve>
     */
    protected function parseInput(): array
    {
        $valves = [];
        $regex = '/^Valve ([A-Z]+) has flow rate=([0-9]+); tunnels? leads? to valves? (.*)$/i';
        foreach ($this->getInputLines() as $line) {
            if (preg_match($regex, $line, $matches)) {
                $valves[$matches[1]] = new Valve($matches[1], $matches[2], explode(', ', $matches[3]));
            }
        }
        Route::$valveDistance = $this->calculateOptimalRoutesBetweenValves($valves);
        return $valves;
    }
}

class Valve
{
    public function __construct(
        public readonly string $name,
        public readonly int $rate,
        public readonly array $connections,
    ){
    }
}

class Route
{
    public static array $valveDistance;

    public function __construct(
        public array $unopenedValves,
        public int $minutesRemaining = 30,
        public string $position = 'AA',
        public int $totalPressure = 0,
    ){
    }

    public function moveAndOpenValve(Valve $valve): bool
    {
        $this->minutesRemaining -= self::$valveDistance[$this->position][$valve->name] + 1;
        if ($this->minutesRemaining < 1) {
            return false;
        }
        unset($this->unopenedValves[$valve->name]);
        $this->position = $valve->name;
        $this->totalPressure += $valve->rate * $this->minutesRemaining;
        return true;
    }
}