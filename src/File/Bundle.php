<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\File;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Ronanchilvers\Bundler\File;
use Traversable;
use InvalidArgumentException;

/**
 * Bundle of files to work on.
 *
 * Implements:
 *  - ArrayAccess       => Direct indexed access ($bundle[0], isset($bundle[2]), unset($bundle[1]), $bundle[] = '/path/to/file.css')
 *  - IteratorAggregate => foreach ($bundle as $fileInstance)
 *  - Countable         => count($bundle)
 *
 * Example:
 *   $bundle = new Bundle(['a.css', 'b.css']);
 *   $bundle[] = 'c.css';
 *   foreach ($bundle as $f) {
 *       // $f is a File instance
 *   }
 *   $total = count($bundle); // 3
 *
 * @implements ArrayAccess<int,\Ronanchilvers\Bundler\File>
 * @implements IteratorAggregate<int,\Ronanchilvers\Bundler\File>
 */
class Bundle implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array<int,File> List of File objects in the bundle
     */
    private array $files = [];

    /**
     * @param iterable<int,File|string> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->addMany($items);
    }

    /**
     * Add a single File or path to the bundle.
     */
    public function add(File|string $file): self
    {
        if ($file instanceof File) {
            $this->files[] = $file;
            return $this;
        }

        $path = trim($file);
        if ($path === '') {
            throw new InvalidArgumentException('Path value must be a non-empty string');
        }
        $this->files[] = new File($path);

        return $this;
    }

    /**
     * Add many File objects or paths.
     *
     * @param iterable<File|string> $items
     */
    public function addMany(iterable $items): self
    {
        foreach ($items as $item) {
            $this->add($item);
        }
        return $this;
    }

    /**
     * Return a plain array of File objects.
     *
     * @return array<int,File>
     */
    public function all(): array
    {
        return $this->files;
    }

    /**
     * Alias for all() for semantic clarity.
     *
     * @return array<int,File>
     */
    public function toArray(): array
    {
        return $this->files;
    }

    /**
     * Countable implementation.
     */
    public function count(): int
    {
        return count($this->files);
    }

    /**
     * IteratorAggregate implementation.
     *
     * @return Traversable<int,File>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->files);
    }

    /**
     * ArrayAccess: Does an index exist?
     *
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->files[$offset]);
    }

    /**
     * ArrayAccess: Get File at index.
     *
     * @param int $offset
     * @return File|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->files[$offset] ?? null;
    }

    /**
     * ArrayAccess: Set File or path at index (or append if null).
     *
     * @param int|null        $offset
     * @param File|string $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof File) {
            if ($offset === null) {
                $this->files[] = $value;
            } else {
                $this->files[$offset] = $value;
            }
            return;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Bundle only accepts File instances or string paths');
        }
        $path = trim($value);
        if ($path === '') {
            throw new InvalidArgumentException('Path value must be a non-empty string');
        }

        $file = new File($path);
        if ($offset === null) {
            $this->files[] = $file;
        } else {
            $this->files[$offset] = $file;
        }
    }

    /**
     * ArrayAccess: Unset index.
     *
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->files[$offset])) {
            unset($this->files[$offset]);
            // Re-index to keep numeric order predictable
            $this->files = array_values($this->files);
        }
    }
}
