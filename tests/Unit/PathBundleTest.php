<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Path\Bundle;

/**
 * @internal
 */
#[CoversClass(Bundle::class)]
final class PathBundleTest extends TestCase
{
    public function testConstructWithInitialPaths(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $this->assertSame(['a.css', 'b.css'], $bundle->paths());
        $this->assertCount(2, $bundle);
    }

    public function testAddSinglePath(): void
    {
        $bundle = new Bundle();
        $bundle->add('one.js');
        $this->assertSame(['one.js'], $bundle->paths());
        $this->assertCount(1, $bundle);
    }

    public function testAddMany(): void
    {
        $bundle = new Bundle();
        $bundle->addMany(['one.js', 'two.js', 'three.js']);
        $this->assertSame(['one.js', 'two.js', 'three.js'], $bundle->paths());
        $this->assertCount(3, $bundle);
    }

    public function testAddEnsuresAssociativeKeysByPath(): void
    {
        $bundle = new Bundle();
        $bundle->add('dup.css');
        $bundle->add('dup.css'); // second add should overwrite same key, not duplicate
        $this->assertSame(['dup.css'], $bundle->paths());
        $this->assertCount(1, $bundle);
    }

    public function testSetAttributeCreatesAttributeArray(): void
    {
        $bundle = new Bundle(['style.css']);
        $this->assertSame([], $bundle->attributes('style.css'));
        $bundle->setAttribute('style.css', 'media', 'screen');
        $bundle->setAttribute('style.css', 'integrity', 'abc123');
        $this->assertSame(
            [
                'media' => 'screen',
                'integrity' => 'abc123',
            ],
            $bundle->attributes('style.css')
        );
    }

    public function testAttributesOnMissingPathReturnsEmptyArray(): void
    {
        $bundle = new Bundle();
        $this->assertSame([], $bundle->attributes('never-there.css'));
    }

    public function testPathsReturnsInsertionOrder(): void
    {
        $paths = ['a.css', 'b.css', 'c.css'];
        $bundle = new Bundle($paths);
        $this->assertSame($paths, $bundle->paths());
    }

    public function testArrayAccessExistsByStringKey(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $this->assertTrue(isset($bundle['a.css']));
        $this->assertFalse(isset($bundle['nope.css']));
    }

    public function testArrayAccessExistsByIntegerIndex(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $this->assertTrue(isset($bundle[0]));
        $this->assertTrue(isset($bundle[1]));
        $this->assertFalse(isset($bundle[2]));
    }

    public function testArrayAccessGetByStringKey(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $this->assertSame('a.css', $bundle['a.css']);
        $this->assertNull($bundle['nope.css']);
    }

    public function testArrayAccessGetByIntegerIndex(): void
    {
        $bundle = new Bundle(['a.css', 'b.css']);
        $this->assertSame('a.css', $bundle[0]);
        $this->assertSame('b.css', $bundle[1]);
        $this->assertNull($bundle[2]);
    }

    public function testArrayAccessAppend(): void
    {
        $bundle = new Bundle(['a.css']);
        $bundle[] = 'b.css';
        $bundle[] = 'c.css';
        $this->assertSame(['a.css', 'b.css', 'c.css'], $bundle->paths());
    }

    public function testArrayAccessEnsurePresenceWithNullAssignment(): void
    {
        $bundle = new Bundle();
        $bundle['new.css'] = null;
        $this->assertSame(['new.css'], $bundle->paths());
    }

    public function testArrayAccessReplacePathKey(): void
    {
        $bundle = new Bundle(['old.css', 'keep.css']);
        $bundle['old.css'] = 'new.css';
        $this->assertSame(['keep.css', 'new.css'], array_values($bundle->paths()));
        $this->assertFalse(isset($bundle['old.css']));
        $this->assertTrue(isset($bundle['new.css']));
    }

    public function testArrayAccessSetWithIntegerOffsetThrows(): void
    {
        $bundle = new Bundle(['a.css']);
        $this->expectException(\InvalidArgumentException::class);
        $bundle[0] = 'b.css';
    }

    public function testArrayAccessUnsetByStringKey(): void
    {
        $bundle = new Bundle(['a.css', 'b.css', 'c.css']);
        unset($bundle['b.css']);
        $this->assertSame(['a.css', 'c.css'], $bundle->paths());
    }

    public function testArrayAccessUnsetByIntegerIndex(): void
    {
        $bundle = new Bundle(['a.css', 'b.css', 'c.css']);
        unset($bundle[1]); // removes b.css
        $this->assertSame(['a.css', 'c.css'], $bundle->paths());
    }

    public function testIterationYieldsPathStrings(): void
    {
        $bundle = new Bundle(['x.js', 'y.js', 'z.js']);
        $collected = [];
        foreach ($bundle as $p) {
            $collected[] = $p;
        }
        $this->assertSame(['x.js', 'y.js', 'z.js'], $collected);
    }

    public function testCountable(): void
    {
        $bundle = new Bundle(['x', 'y', 'z']);
        $this->assertCount(3, $bundle);
    }

    public function testClearingByReinitialisation(): void
    {
        $bundle = new Bundle(['a', 'b']);
        // Simulate clearing by constructing new (class has no explicit clear method)
        $bundle = new Bundle();
        $this->assertCount(0, $bundle);
    }
}
