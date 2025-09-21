<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class Manifest
{
    protected $data = [];

    public function __construct()
    {
    }

    public function add(string $path, string $data): static
    {
        $this->data[$path] = $data;

        return $this;
    }
}
