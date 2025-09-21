<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Decorator;

use Ronanchilvers\Bundler\Output\Traits\FileTrait;
use Ronanchilvers\Bundler\Path\Bundle;

class Concatenate extends Decorator
{
    use FileTrait;

    protected function modifyPaths(Bundle $paths): Bundle
    {
        $source = rtrim($this->getConfig('source'), DIRECTORY_SEPARATOR);
        $destination = rtrim($this->getConfig('destination'), DIRECTORY_SEPARATOR);
        if (!is_dir($destination)) {
            throw new \RuntimeException(
                sprintf('Destination %s is not a valid directory', $destination)
            );
        }
        $content = [];
        $extension = null;
        foreach ($paths as $path) {
            if (is_null($extension)) {
                $extension = $extension ?: pathinfo((string) $path, PATHINFO_EXTENSION);
            }
            $sourcePath = $this->joinPaths(
                $source,
                $path,
            );
            if (!file_exists($sourcePath)) {
                throw new \RuntimeException(
                    sprintf('Source file %s does not exist', $sourcePath)
                );
            }
            $content[] = file_get_contents(
                filename: $sourcePath,
            );
        }
        $content = implode("\n", $content);
        $hash = hash(
            algo: 'crc32c',
            data: $content,
        );
        $filename = sprintf(
            '%s-%s.%s',
            $this->getConfig('bundle_basename', 'bundle'),
            $hash,
            $extension,
        );
        $destinationFilename = $this->joinPaths($destination, $filename);
        if (!$this->writeFile($destinationFilename, $content)) {
            throw new \RuntimeException(
                sprintf('Could not write to file %s', $destinationFilename)
            );
        }

        $path = sprintf(
            '%s%s%s',
            $this->getConfig('web_path'),
            DIRECTORY_SEPARATOR,
            $filename
        );

        return new Bundle([ $path ]);
    }
}
