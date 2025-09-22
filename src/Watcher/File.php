<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Watcher;

use Closure;

class File
{
    protected int $interval;
    protected array $files = [];

    public function __construct(int $interval = 250)
    {
        $this->interval = $interval;
    }

    public function addFile(string $filename): static
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $filename));
        }
        $this->files[$filename] = filemtime($filename);

        return $this;
    }

    public function start(Closure $closure): bool
    {
        while (true) {
            clearstatcache();
            foreach ($this->files as $filename => $lastModified) {
                if (filemtime($filename) !== $lastModified) {
                    $closure($filename);
                }
                $this->files[$filename] = filemtime($filename);
            }
            usleep($this->interval);
        }

        return true;
    }
}
