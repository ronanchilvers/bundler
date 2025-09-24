<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Decorator;

use Ronanchilvers\Bundler\Format\Traits\FileTrait;
use Ronanchilvers\Bundler\Path;
use Ronanchilvers\Bundler\Path\Bundle;

class Concatenate extends Decorator
{
    use FileTrait;

    protected function modifyPaths(Bundle $paths): Bundle
    {
        $source = Path::placeholders(rtrim($this->getConfig('source'), DIRECTORY_SEPARATOR));
        $destination = Path::placeholders(rtrim($this->getConfig('destination'), DIRECTORY_SEPARATOR));
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
            if (!file_exists($path)) {
                throw new \RuntimeException(
                    sprintf('Source file %s does not exist', $path)
                );
            }
            $content[] = file_get_contents(
                filename: $path,
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

        $bundle = clone $paths;
        $bundle->clear();

        return $bundle->add($destinationFilename);
    }
}
