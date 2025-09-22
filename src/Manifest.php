<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Path\Bundle;

class Manifest
{
    protected $data = [];

    public function __construct()
    {
    }

    public function add(string $name, Bundle $bundle): static
    {
        $this->data[$name] = $bundle;

        return $this;
    }

    public function store(string $filename): bool
    {
        $json = [];
        foreach ($this->data as $name => $bundle) {
            $json[$name] = $bundle->toArray();
        }

        return file_put_contents($filename, json_encode($json)) !== false;
    }
}
