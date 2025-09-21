<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Output\Decorator\SRI;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Path\Bundle;

/**
 * Tests for the SRI (Subresource Integrity) decorator.
 *
 * The decorator adds integrity and crossorigin attributes
 * to each path in the bundle based on configured hashing algorithms.
 */
#[CoversClass(SRI::class)]
final class SRIDecoratorTest extends TestCase
{
    /** @var list<string> */
    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_dir($path) && !is_link($path)) {
                $this->recursiveRmDir($path);
            } elseif (file_exists($path)) {
                @unlink($path);
            }
        }
        $this->cleanup = [];
    }

    public function testDefaultAlgorithmSha384AddedToIntegrityAttribute(): void
    {
        $sourceDir = $this->makeTempDir();
        $fileName  = 'style.css';
        $content   = "body { color: #333; }";
        $this->writeFile($sourceDir . DIRECTORY_SEPARATOR . $fileName, $content);

        $formatter = (new Stylesheet())->decorate(SRI::class, [
            'source' => $sourceDir,
            // algorithms omitted => default ['sha384']
        ]);

        $bundle = new Bundle([$fileName]);
        $html   = $formatter->render($bundle);

        // Expect one link tag & integrity attribute with sha384 prefix
        $this->assertSame(1, substr_count($html, '<link '), 'One link tag expected');
        $this->assertStringContainsString('integrity="sha384-', $html);
        $this->assertStringContainsString('crossorigin="anonymous"', $html);

        // Verify hash value correctness
        $expectedHash = base64_encode(hash_file('sha384', $sourceDir . '/' . $fileName, true));
        $this->assertStringContainsString('sha384-' . $expectedHash, $html);
    }

    public function testMultipleAlgorithmsProduceSpaceSeparatedIntegrityValue(): void
    {
        $sourceDir = $this->makeTempDir();
        $fileName  = 'app.js';
        $content   = "console.log('ok');";
        $this->writeFile($sourceDir . DIRECTORY_SEPARATOR . $fileName, $content);

        $algorithms = ['sha256', 'sha384'];
        $formatter = (new Stylesheet())->decorate(SRI::class, [
            'source'     => $sourceDir,
            'algorithms' => $algorithms,
        ]);

        $bundle = new Bundle([$fileName]);
        $html   = $formatter->render($bundle);

        $this->assertStringContainsString('integrity="', $html);
        foreach ($algorithms as $algo) {
            $expectedHash = base64_encode(hash_file($algo, $sourceDir . '/' . $fileName, true));
            $this->assertStringContainsString($algo . '-' . $expectedHash, $html);
        }

        // Ensure a space separates the two algorithm entries
        $this->assertMatchesRegularExpression(
            '/integrity="sha256-[A-Za-z0-9+\/=]+ sha384-[A-Za-z0-9+\/=]+"/',
            $html
        );
        $this->assertStringContainsString('crossorigin="anonymous"', $html);
    }

    public function testEmptyAlgorithmListResultsInNoIntegrityAttribute(): void
    {
        $sourceDir = $this->makeTempDir();
        $fileName  = 'plain.css';
        $this->writeFile($sourceDir . DIRECTORY_SEPARATOR . $fileName, 'h1 { font-size: 2rem; }');

        $formatter = (new Stylesheet())->decorate(SRI::class, [
            'source'     => $sourceDir,
            'algorithms' => [], // explicitly empty
        ]);

        $bundle = new Bundle([$fileName]);
        $html   = $formatter->render($bundle);

        $this->assertStringNotContainsString('integrity="', $html);
        $this->assertStringNotContainsString('crossorigin="anonymous"', $html);
    }

    public function testThrowsIfSourceDirectoryInvalid(): void
    {
        $invalidSource = $this->makeTempDir() . '/not_a_dir';
        $formatter = (new Stylesheet())->decorate(SRI::class, [
            'source' => $invalidSource,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not a valid directory');

        $formatter->render(new Bundle(['x.css']));
    }

    public function testThrowsIfFileMissingInSource(): void
    {
        $sourceDir = $this->makeTempDir();
        // Only create one file
        $this->writeFile($sourceDir . '/exists.css', 'p { margin: 0; }');

        $formatter = (new Stylesheet())->decorate(SRI::class, [
            'source' => $sourceDir,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not exist');

        $formatter->render(new Bundle(['exists.css', 'missing.css']));
    }

    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bundler_sri_' . uniqid('', true);
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->fail('Could not create temp directory: ' . $dir);
        }
        $this->cleanup[] = $dir;
        return $dir;
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->fail('Could not create directory for file: ' . $dir);
        }
        if (file_put_contents($path, $content) === false) {
            $this->fail('Failed writing test file: ' . $path);
        }
        $this->cleanup[] = $path;
    }

    private function recursiveRmDir(string $dir): void
    {
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path) && !is_link($path)) {
                $this->recursiveRmDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
