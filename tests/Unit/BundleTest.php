<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\File\Bundle;
use Ronanchilvers\Bundler\File;

#[CoversClass(Bundle::class)]
final class BundleTest extends TestCase
{
    public function testConstructWithInitialFiles(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['a.css', 'b.css'], $paths);
        $this->assertCount(2, $bundle);
    }

    public function testAddSingleFile(): void
    {
        $bundle = new Bundle();
        $bundle->add('one.js');
        $paths = array_map(fn($f) => $f->path(), $bundle->toArray());
        $this->assertSame(['one.js'], $paths);
        $this->assertCount(1, $bundle);
    }

    public function testAddMany(): void
    {
        $bundle = new Bundle();
        $bundle->addMany(['one.js', 'two.js', 'three.js']);
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['one.js', 'two.js', 'three.js'], $paths);
        $this->assertCount(3, $bundle);
    }

    public function testAddTrimsWhitespace(): void
    {
        $bundle = new Bundle();
        $bundle->add("  file.css  ");
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['file.css'], $paths);
    }

    public function testArrayAccessAppendAndRetrieve(): void
    {
        $bundle = new Bundle();
        $bundle[] = 'a.css';
        $bundle[] = 'b.css';

        $this->assertSame('a.css', $bundle[0]->path());
        $this->assertSame('b.css', $bundle[1]->path());
        $this->assertTrue(isset($bundle[1]));
        $this->assertFalse(isset($bundle[2]));
    }

    public function testArrayAccessSetAtIndex(): void
    {
        $bundle = new Bundle(['x.css']);
        $bundle[0] = 'y.css';
        $this->assertSame('y.css', $bundle[0]->path());
        $bundle[1] = 'z.css';
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['y.css', 'z.css'], $paths);
    }

    public function testArrayAccessUnsetReindexes(): void
    {
        $bundle = new Bundle(['a.css', 'b.css', 'c.css']);
        unset($bundle[1]); // remove b.css
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['a.css', 'c.css'], $paths);
        // After reindexing, 'c.css' should now be at index 1
        $this->assertSame('c.css', $bundle[1]->path());
        $this->assertFalse(isset($bundle[2]));
    }

    public function testIteration(): void
    {
        $bundle = new Bundle(['a.js', 'b.js', 'c.js']);
        $collected = [];
        foreach ($bundle as $file) {
            $collected[] = $file->path();
        }
        $this->assertSame(['a.js', 'b.js', 'c.js'], $collected);
    }

    public function testCountable(): void
    {
        $bundle = new Bundle(['a', 'b', 'c']);
        $this->assertCount(3, $bundle);
    }

    public function testRejectsEmptyStringOnAdd(): void
    {
        $bundle = new Bundle();
        $this->expectException(InvalidArgumentException::class);
        $bundle->add('');
    }

    public function testRejectsEmptyStringOnArrayAccess(): void
    {
        $bundle = new Bundle();
        $this->expectException(InvalidArgumentException::class);
        $bundle[] = '   ';
    }

    public function testRejectsNonStringOnArrayAccess(): void
    {
        $bundle = new Bundle();
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bundle[] = 123; // non-string should trigger exception
    }

    public function testConstructWithTraversable(): void
    {
        $iter = new \ArrayIterator(['a.scss', 'b.scss']);
        $bundle = new Bundle($iter);
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['a.scss', 'b.scss'], $paths);
    }

    public function testAddManyWithTraversable(): void
    {
        $bundle = new Bundle();
        $gen = (function () {
            yield 'one.css';
            yield 'two.css';
        })();
        $bundle->addMany($gen);
        $paths = array_map(fn($f) => $f->path(), $bundle->all());
        $this->assertSame(['one.css', 'two.css'], $paths);
    }
}
