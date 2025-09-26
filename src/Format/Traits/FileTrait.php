<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Traits;

trait FileTrait
{
    /**
     * @param mixed $paths
     */
    protected function joinPaths(...$paths): string
    {
        $path = implode(
            array: $paths,
            separator: DIRECTORY_SEPARATOR,
        );

        return str_replace(
            search: DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
            replace: DIRECTORY_SEPARATOR,
            subject: $path,
        );
    }

    protected function writeFile(string $filename, string $content): bool
    {
        return file_put_contents($filename, $content) !== false;
    }
}
