<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Output\Decorator\Concatenate;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Output\Element\Script;
use Ronanchilvers\Bundler\Path\Bundle;

/**
 * Tests for the Concatenate decorator which bundles multiple assets
 * into a single file with a hash in the filename.
 */
#[CoversClass(Concatenate::class)]
final class ConcatenateDecoratorTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanupPaths as $path) {
            if (is_dir($path) && !is_link($path)) {
                $this->recursiveRmDir($path);
            } elseif (file_exists($path)) {
                @unlink($path);
            }
        }
        $this->cleanupPaths = [];
    }

    public function testConcatenateGeneratesBundledStylesheet(): void
    {
        $source = $this->makeTempDir();
        $dest   = $this->makeTempDir();
        $webPath = '/assets/css';

        $cssA = "body { color: #111; }";
        $cssB = ".x { padding: 1rem; }";

        $this->writeFile($source . '/app.css', $cssA);
        $this->writeFile($source . '/extra.css', $cssB);

        $formatter = (new Stylesheet())->decorate(Concatenate::class, [
            'source'          => $source,
            'destination'     => $dest,
            'web_path'        => $webPath,
            'bundle_basename' => 'styles',
        ]);

        $inputBundle = new Bundle(['app.css', 'extra.css']);

        $html = $formatter->render($inputBundle);

        // Single link tag expected
        $this->assertSame(1, substr_count($html, '<link '), 'Exactly one bundled <link> tag expected');

        // Contains path prefix and bundle basename
        $this->assertStringContainsString($webPath . '/styles-', $html);

        // Match hashed filename pattern (crc32c => 8 hex chars)
        $this->assertMatchesRegularExpression(
            '#href="/assets/css/styles-[a-f0-9]{8}\.css"#',
            $html,
            'Href should contain hashed filename'
        );

        // Destination should contain exactly one generated file
        $files = glob($dest . DIRECTORY_SEPARATOR . 'styles-*.css');
        $this->assertIsArray($files);
        $this->assertCount(1, $files, 'One concatenated file expected in destination');
        $generatedFile = $files[0];
        $this->assertFileExists($generatedFile);
        $this->assertSame($cssA . "\n" . $cssB, file_get_contents($generatedFile));
    }

    public function testConcatenateGeneratesBundledScript(): void
    {
        $source = $this->makeTempDir();
        $dest   = $this->makeTempDir();
        $webPath = '/assets/js';

        $jsA = "console.log('alpha');";
        $jsB = "console.log('beta');";

        $this->writeFile($source . '/alpha.js', $jsA);
        $this->writeFile($source . '/beta.js', $jsB);

        $formatter = (new Script())->decorate(Concatenate::class, [
            'source'          => $source,
            'destination'     => $dest,
            'web_path'        => $webPath,
            'bundle_basename' => 'app',
        ]);

        $inputBundle = new Bundle(['alpha.js', 'beta.js']);

        $html = $formatter->render($inputBundle);

        $this->assertSame(1, substr_count($html, '<script '), 'Exactly one bundled <script> tag expected');
        $this->assertStringContainsString($webPath . '/app-', $html);
        $this->assertMatchesRegularExpression(
            '#src="/assets/js/app-[a-f0-9]{8}\.js"#',
            $html
        );

        $files = glob($dest . DIRECTORY_SEPARATOR . 'app-*.js');
        $this->assertIsArray($files);
        $this->assertCount(1, $files);
        $this->assertSame($jsA . "\n" . $jsB, file_get_contents($files[0]));
    }

    public function testConcatenateThrowsIfDestinationIsNotDirectory(): void
    {
        $source = $this->makeTempDir();
        $notADir = $source . '/no_such_dir'; // Does not exist
        $webPath = '/assets/css';

        // Valid source file
        $this->writeFile($source . '/file.css', "h1 { font-weight: bold; }");

        $formatter = (new Stylesheet())->decorate(Concatenate::class, [
            'source'      => $source,
            'destination' => $notADir,
            'web_path'    => $webPath,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not a valid directory');

        $formatter->render(new Bundle(['file.css']));
    }

    public function testConcatenateThrowsIfSourceFileMissing(): void
    {
        $source = $this->makeTempDir();
        $dest   = $this->makeTempDir();
        $webPath = '/assets/css';

        // Only create one of the declared files
        $this->writeFile($source . '/exists.css', "p { margin: 0; }");

        $formatter = (new Stylesheet())->decorate(Concatenate::class, [
            'source'      => $source,
            'destination' => $dest,
            'web_path'    => $webPath,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not exist');

        $formatter->render(new Bundle(['exists.css', 'missing.css']));
    }

    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bundler_concat_' . uniqid('', true);
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->fail('Unable to create temp directory: ' . $dir);
        }
        $this->cleanupPaths[] = $dir;
        return $dir;
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->fail('Cannot create directory for file: ' . $dir);
        }
        if (file_put_contents($path, $content) === false) {
            $this->fail('Failed writing file: ' . $path);
        }
        $this->cleanupPaths[] = $path;
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
