<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Decorator;

use Ronanchilvers\Bundler\Format\Decorator;
use Ronanchilvers\Bundler\Format\Traits\FileTrait;
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
            if (!file_exists($path)) {
                throw new \RuntimeException(
                    sprintf('Source file %s does not exist', $path)
                );
            }
            $algorithms = $this->getConfig('algorithms', ['sha384']);
            if (empty($algorithms)) {
                return $bundle;
            }

            $hashes = [];
            foreach ($algorithms as $algorithm) {
                $hash = base64_encode(hash_file($algorithm, $path, true));
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
