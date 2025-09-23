<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Path;

use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\EventNames;

/**
 * @implements \ArrayAccess<int,string>
 * @implements \IteratorAggregate<int,string>
 */
class Bundle implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected array $paths = [];
    protected ?string $name = null;
    protected ?Dispatcher $events = null;

    /**
     * @param array<int,string> $paths Initial path list (optional)
     * @param string|null $name  Logical bundle name for events
     */
    public function __construct(
        array $paths = [],
        ?string $name = null,
        ?Dispatcher $events = null,
    ) {
        $this->name = $name;
        $this->events = $events;
        $this->addMany($paths);
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function add(string $path): static
    {
        $this->events?->emit(EventNames::CONFIG_FILE_ADDING, [
            $path,
        ]);
        $this->paths[$path] = [
            "path" => $path,
        ];
        $this->events?->emit(EventNames::CONFIG_FILE_ADDED, [
            $path,
        ]);

        return $this;
    }

    /**
     * @param array<int,mixed> $paths
     */
    public function addMany(array $paths): static
    {
        foreach ($paths as $path) {
            $this->add($path);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clear(): static
    {
        $this->paths = [];

        return $this;
    }

    /**
     * @param mixed $path
     * @param mixed $key
     * @param mixed $value
     */
    public function setAttribute($path, $key, $value): static
    {
        if (!isset($this->paths[$path])) {
            $this->paths[$path] = [
                "path" => $path,
            ];
        }
        $this->paths[$path]["attributes"][$key] = $value;

        return $this;
    }

    public function attributes(string $path): array
    {
        return $this->paths[$path]["attributes"] ?? [];
    }

    /**
     * Return all path strings.
     *
     * @return array<int,string>
     */
    public function paths(): array
    {
        return array_keys($this->paths);
    }

    public function toArray(): array
    {
        return $this->paths;
    }

    /**
     * Countable: number of paths in the bundle.
     */
    public function count(): int
    {
        return count($this->paths);
    }

    /**
     * IteratorAggregate: iterate over path strings.
     *
     * @return \Traversable<int,string>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->paths());
    }

    /**
     * ArrayAccess: does the given offset exist?
     *
     * Supports:
     *  - string offset (path key)
     *  - integer offset (index into ordered path list)
     */
    public function offsetExists(mixed $offset): bool
    {
        if (is_string($offset)) {
            return isset($this->paths[$offset]);
        }
        if (is_int($offset)) {
            $keys = array_keys($this->paths);
            return isset($keys[$offset]);
        }
        return false;
    }

    /**
     * ArrayAccess: get path by offset.
     *
     * @return string|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset)) {
            return $this->paths[$offset]["path"] ?? null;
        }
        if (is_int($offset)) {
            $keys = array_keys($this->paths);
            return $keys[$offset] ?? null;
        }
        return null;
    }

    /**
     * ArrayAccess: set path.
     *
     * Semantics:
     *  - $bundle[] = 'file.css'              (append)
     *  - $bundle['file.css'] = null          (ensure present)
     *  - $bundle['file.css'] = 'other.css'   (replace mapping to other path)
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException(
                    "Appending requires a string path",
                );
            }
            $this->add($value);
            return;
        }

        if (is_int($offset)) {
            // Integer offsets not directly settable (ambiguous) – require string
            throw new \InvalidArgumentException(
                "Integer offsets not supported for assignment; use string path keys or append",
            );
        }

        if (!is_string($offset)) {
            throw new \InvalidArgumentException(
                "Offset must be a string (path)",
            );
        }

        // If value omitted / null => ensure offset path exists
        if ($value === null) {
            $this->add($offset);
            return;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                "Assigned value must be a string path or null",
            );
        }

        // Replace (remove old key if different) and add new path
        if ($offset !== $value) {
            unset($this->paths[$offset]);
        }
        $this->add($value);
    }

    /**
     * ArrayAccess: unset offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) {
            unset($this->paths[$offset]);
            return;
        }
        if (is_int($offset)) {
            $keys = array_keys($this->paths);
            if (isset($keys[$offset])) {
                unset($this->paths[$keys[$offset]]);
            }
        }
    }
}
