<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day02 extends AbstractSolution
{
    public const ROCK     = 1;
    public const PAPER    = 2;
    public const SCISSORS = 3;

    public const LOSE = 'X';
    public const DRAW = 'Y';
    public const WIN  = 'Z';

    protected array $inputMap = [
        'A' => self::ROCK,
        'B' => self::PAPER,
        'C' => self::SCISSORS,
    ];

    protected array $loseMap;
    protected array $winMap = [
        self::ROCK     => self::SCISSORS,
        self::PAPER    => self::ROCK,
        self::SCISSORS => self::PAPER,
    ];

    protected function solvePart1(): string
    {
        $responseMap = [
            'X' => self::ROCK,
            'Y' => self::PAPER,
            'Z' => self::SCISSORS,
        ];
        $score = 0;
        foreach ($this->getInputLines() as $round) {
            $moves = explode(' ', $round);
            $score += $this->playRound($this->inputMap[$moves[0]], $responseMap[$moves[1]]);
        }
        return $score;
    }

    protected function solvePart2(): string
    {
        $this->loseMap = array_flip($this->winMap);
        $score = 0;
        foreach ($this->getInputLines() as $round) {
            $moves = explode(' ', $round);
            $score += $this->calculateMoveScore($this->inputMap[$moves[0]], $moves[1]);
        }
        return $score;
    }

    protected function playRound(int $move1, int $move2): int
    {
        $score = $move2;
        if ($this->winMap[$move1] != $move2) {
            if ($this->winMap[$move2] == $move1) {
                $score += 6;
            } else {
                $score += 3;
            }
        }
        return $score;
    }

    protected function calculateMoveScore(int $move1, string $strategy): int
    {
        switch ($strategy) {
            case self::WIN:
                return 6 + $this->loseMap[$move1];
            case self::DRAW:
                return 3 + $move1;
            case self::LOSE:
                return $this->winMap[$move1];
        }
    }
}
