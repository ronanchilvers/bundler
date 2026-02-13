<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class Registry
{
    private array $items = [];

    public function set(string $name, mixed $formatter): void
    {
        $this->items[$name] = $formatter;
    }

    public function get(string $name): mixed
    {
        if (!array_key_exists($name, $this->items)) {
            return null;
        }

        return $this->items[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->items);
    }
}
