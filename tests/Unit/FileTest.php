<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\File;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    /**
     * Keep track of any temp files we create so we can clean them up.
     *
     * @var list<string>
     */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($tempFiles = $this->tempFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->tempFiles = [];
    }

    public function testPathReturnsOriginalValue(): void
    {
        $path = '/tmp/nonexistent_' . uniqid('', true) . '.txt';
        $file = new File($path);

        $this->assertSame($path, $file->path());
    }

    public function testExistsReturnsFalseForMissingFile(): void
    {
        $path = $this->uniqueTempPath();
        $file = new File($path);

        $this->assertFalse($file->exists());
    }

    public function testExistsReturnsTrueForExistingFile(): void
    {
        $path = $this->createTempFile('hello');
        $file = new File($path);

        $this->assertTrue($file->exists());
    }

    public function testContentReturnsEmptyStringForMissingFile(): void
    {
        $path = $this->uniqueTempPath();
        $file = new File($path);

        $this->assertSame('', $file->content());
    }

    public function testContentReturnsFileContents(): void
    {
        $content = "Line1\nLine2";
        $path = $this->createTempFile($content);
        $file = new File($path);

        $this->assertSame($content, $file->content());
    }

    public function testSizeReturnsZeroForMissingFile(): void
    {
        $path = $this->uniqueTempPath();
        $file = new File($path);

        $this->assertSame(0, $file->size());
    }

    public function testSizeReturnsFileSize(): void
    {
        $content = "abcdef"; // 6 bytes
        $path = $this->createTempFile($content);
        $file = new File($path);

        $this->assertSame(strlen($content), $file->size());
    }

    public function testDirReturnsDirectoryComponent(): void
    {
        $path = $this->createTempFile('data');
        $file = new File($path);

        $this->assertSame(dirname($path), $file->dir());
    }

    public function testEmptyFileHasZeroSizeAndEmptyContent(): void
    {
        $path = $this->createTempFile('');
        $file = new File($path);

        $this->assertSame(0, $file->size());
        $this->assertSame('', $file->content());
    }

    /**
     * Create a temporary file with provided content and track it for cleanup.
     */
    private function createTempFile(string $content): string
    {
        $path = $this->uniqueTempPath();
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * Generate a unique path (does not create the file).
     */
    private function uniqueTempPath(): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR .
            'bundler_test_' .
            uniqid('', true);
    }
}
