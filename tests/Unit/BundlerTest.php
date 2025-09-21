<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Bundler;
use Ronanchilvers\Bundler\Output\Decorator\Concatenate;
use Ronanchilvers\Bundler\Output\Element\Script;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Path\Bundle as PathBundle;

#[CoversClass(Bundler::class)]
final class BundlerTest extends TestCase
{
    private array $tempPaths = [];

    protected function tearDown(): void
    {
        // Clean up any temp files / directories created
        foreach ($this->tempPaths as $path) {
            if (is_dir($path) && !is_link($path)) {
                $this->recursiveRmDir($path);
            } elseif (file_exists($path)) {
                @unlink($path);
            }
        }
        $this->tempPaths = [];
    }

    public function testStylesheetReturnsStylesheetFormatter(): void
    {
        $formatter = Bundler::stylesheet();
        $this->assertInstanceOf(FormatterInterface::class, $formatter);
        $this->assertInstanceOf(Stylesheet::class, $formatter);
    }

    public function testScriptReturnsScriptFormatter(): void
    {
        $formatter = Bundler::script();
        $this->assertInstanceOf(FormatterInterface::class, $formatter);
        $this->assertInstanceOf(Script::class, $formatter);
    }

    public function testFormattersAreIndependentInstances(): void
    {
        $a = Bundler::stylesheet();
        $b = Bundler::stylesheet();
        $this->assertNotSame($a, $b);
    }

    public function testStylesheetRendersMultiplePathsWithEscaping(): void
    {
        $formatter = Bundler::stylesheet();
        $bundle = new PathBundle([
            'css/app.css',
            'css/theme&v=1.css',
        ]);
        $html = $formatter->render($bundle);

        // Should contain two link tags
        $this->assertEquals(2, substr_count($html, '<link '));

        // Each path present (escaped where needed)
        $this->assertStringContainsString('href="css/app.css"', $html);
        $this->assertStringContainsString('href="css/theme&amp;v=1.css"', $html);

        // Basic structural regex checks
        $this->assertMatchesRegularExpression('/<link[^>]+href="css\/app\.css"/', $html);
        $this->assertMatchesRegularExpression('/<link[^>]+href="css\/theme&amp;v=1\.css"/', $html);
    }

    public function testScriptRendersMultiplePaths(): void
    {
        $formatter = Bundler::script();
        $bundle = new PathBundle([
            'js/app.js',
            'js/vendor/lib.js',
        ]);
        $html = $formatter->render($bundle);

        $this->assertEquals(2, substr_count($html, '<script '));
        $this->assertStringContainsString('src="js/app.js"', $html);
        $this->assertStringContainsString('src="js/vendor/lib.js"', $html);
    }

    public function testStylesheetConcatenateDecoratorProducesSingleBundledAsset(): void
    {
        $sourceDir = $this->createTempDir();
        $destDir   = $this->createTempDir();
        $webPath   = '/assets/css';

        // Create sample source files
        $this->writeFile($sourceDir . '/app.css', "body { color: #111; }");
        $this->writeFile($sourceDir . '/extra.css', ".x { padding: 1rem; }");

        $stylesheet = Bundler::stylesheet();
        $this->assertInstanceOf(Stylesheet::class, $stylesheet);
        $formatter = $stylesheet->decorate(Concatenate::class, [
            'source'      => $sourceDir,
            'destination' => $destDir,
            'web_path'    => $webPath,
            // Using defaults for bundle_basename
        ]);

        $html = $formatter->render(new PathBundle([
            'app.css',
            'extra.css',
        ]));

        // Should produce a single link tag
        $this->assertEquals(1, substr_count($html, '<link '));

        // Extract the href path
        $this->assertMatchesRegularExpression(
            '#<link[^>]+href="' . preg_quote($webPath, '#') . '/bundle-[a-f0-9]{8}\.css"#',
            $html
        );

        // Ensure file was written to destination directory
        $writtenFiles = glob($destDir . DIRECTORY_SEPARATOR . 'bundle-*.css');
        $this->assertNotFalse($writtenFiles);
        $this->assertCount(1, $writtenFiles, 'Exactly one bundled file expected in destination directory');
        $this->assertGreaterThan(0, filesize($writtenFiles[0]));
    }

    public function testScriptConcatenateDecoratorWithCustomBasename(): void
    {
        $sourceDir = $this->createTempDir();
        $destDir   = $this->createTempDir();
        $webPath   = '/assets/js';

        $this->writeFile($sourceDir . '/alpha.js', "console.log('alpha');");
        $this->writeFile($sourceDir . '/beta.js', "console.log('beta');");

        $script = Bundler::script();
        $this->assertInstanceOf(Script::class, $script);
        $formatter = $script->decorate(Concatenate::class, [
            'source'          => $sourceDir,
            'destination'     => $destDir,
            'web_path'        => $webPath,
            'bundle_basename' => 'app-bundle',
        ]);

        $bundle = new PathBundle(['alpha.js', 'beta.js']);
        $html = $formatter->render($bundle);

        $this->assertEquals(1, substr_count($html, '<script '));

        $this->assertMatchesRegularExpression(
            '#<script[^>]+src="' . preg_quote($webPath, '#') . '/app-bundle-[a-f0-9]{8}\.js"#',
            $html
        );

        $writtenFiles = glob($destDir . DIRECTORY_SEPARATOR . 'app-bundle-*.js');
        $this->assertNotFalse($writtenFiles);
        $this->assertCount(1, $writtenFiles);
        $this->assertGreaterThan(0, filesize($writtenFiles[0]));
    }

    private function createTempDir(): string
    {
        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bundler_test_' . uniqid('', true);
        if (!mkdir($base, 0777, true) && !is_dir($base)) {
            $this->fail('Could not create temporary directory: ' . $base);
        }
        $this->tempPaths[] = $base;

        return $base;
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                $this->fail('Could not create directory for file: ' . $dir);
            }
        }
        if (file_put_contents($path, $content) === false) {
            $this->fail('Failed writing temp file: ' . $path);
        }
        $this->tempPaths[] = $path;
    }

    private function recursiveRmDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->recursiveRmDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
