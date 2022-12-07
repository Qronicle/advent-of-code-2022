<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Exception;

class Day07 extends AbstractSolution
{
    /** @var Dir[] */
    protected array $dirs = [];

    protected function solvePart1(): string
    {
        $this->parseInput();
        $total = 0;
        foreach ($this->dirs as $dir) {
            if ($dir->size < 100000) {
                $total += $dir->size;
            }
        }
        return $total;
    }

    protected function solvePart2(): string
    {
        $this->parseInput();
        $required = 30000000 - (70000000 - $this->dirs['/']->size);
        $size = null;
        foreach ($this->dirs as $dir) {
            if ($dir->size > $required && (!$size || $dir->size < $size)) {
                $size = $dir->size;
            }
        }
        return $size;
    }

    protected function parseInput(): void
    {
        $dir = new Dir('root');
        $this->dirs['/'] = $dir;
        $lines = $this->getInputLines();
        $l = 0;
        while ($lines) {
            $line = array_shift($lines); $l++;
            if ($line[0] != '$') {
                throw new Exception("Expected user input on line $l");
            }
            switch (substr($line, 2, 2)) {
                case 'cd':
                    $dirName = substr($line, 5);
                    if ($dirName == '..') {
                        if (!$dir->dir) {
                            throw new Exception('Tried to go up at root level at line ' . ($l + 1));
                        }
                        $dir = $dir->dir;
                    } else {
                        $dir = $this->dirs[$dir->path . '/' . $dirName] ?? new Dir($dirName, $dir);
                        $this->dirs[$dir->path] = $dir;
                    }
                    continue 2;
                case 'ls':
                    while ($lines && array_first($lines)[0] != '$') {
                        $out = array_shift($lines); $l++;
                        if ($out[0] != 'd') {
                            $info = explode(' ', $out);
                            $dir->addFile(new File($info[0], $info[1]));
                        }
                    }
                    continue 2;
            }
        }
    }
}

class File {
    public function __construct(
        public int $size,
        public string $name
    ) {
    }
}

class Dir {
    public function __construct(
        public string $name,
        public ?Dir $dir = null,
        public int $size = 0,
        public ?string $path = null,
    ) {
        if (!isset($this->path)) {
            $this->path = $this->dir?->path . '/' . $this->name;
        }
    }

    public function addFile(File $file): self
    {
        $this->size += $file->size;
        $this->dir?->addFile($file);
        return $this;
    }
}