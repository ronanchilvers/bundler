<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class File
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function content(): string
    {
        if (!$this->exists()) {
            return '';
        }
        return file_get_contents($this->path) ?: '';
    }

    public function size(): int
    {
        if (!$this->exists()) {
            return 0;
        }
        return filesize($this->path) ?: 0;
    }

    public function dir(): string
    {
        return dirname($this->path);
    }
}
