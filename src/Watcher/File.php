<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Watcher;

use Closure;

class File
{
    protected $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function start(Closure $closure): bool
    {
        $closure($this->filename);
        while (true) {
            clearstatcache();
            $lastModified = filemtime($this->filename);
            usleep(250);
            clearstatcache();
            if (filemtime($this->filename) !== $lastModified) {
                $closure($this->filename);
            }
        }

        return true;
    }
}
