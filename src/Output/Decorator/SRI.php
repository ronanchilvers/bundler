<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Decorator;

use Ronanchilvers\Bundler\Output\Decorator\Decorator;
use Ronanchilvers\Bundler\Output\Traits\FileTrait;
use Ronanchilvers\Bundler\Path\Bundle;

class SRI extends Decorator
{
    use FileTrait;

    protected function modifyPaths(Bundle $bundle): Bundle
    {
        $source = rtrim($this->getConfig('source'));
        if (!is_dir($source)) {
            throw new \RuntimeException(
                sprintf('Source %s is not a valid directory', $source)
            );
        }
        foreach ($bundle as $path) {
            $sourcePath = $this->joinPaths(
                $source,
                $path,
            );
            if (!file_exists($sourcePath)) {
                throw new \RuntimeException(
                    sprintf('Source file %s does not exist', $sourcePath)
                );
            }
            $algorithms = $this->getConfig('algorithms', ['sha384']);
            if (empty($algorithms)) {
                return $bundle;
            }

            $hashes = [];
            foreach ($algorithms as $algorithm) {
                $hash = base64_encode(hash_file($algorithm, $sourcePath, true));
                $hashes[] = sprintf('%s-%s', $algorithm, $hash);
            }
            $bundle->setAttribute(
                (string)$path,
                'integrity',
                implode(' ', $hashes),
            );
            $bundle->setAttribute(
                (string)$path,
                'crossorigin',
                'anonymous',
            );
        }

        return $bundle;
    }
}
