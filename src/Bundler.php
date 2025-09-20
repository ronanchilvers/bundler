<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\File;
use Ronanchilvers\Bundler\File\Bundle;

class Bundler
{
    private static $config = [];

    /**
     * @param array<int,mixed> $array
     */
    public static function config(array $array): void
    {
        self::$config = array_merge(self::$config, $array);
    }

    /**
     * @param array<int,string> $files
     * @return string
     */
    public static function stylesheet(array $files): string
    {
        $bundle = new File\Bundle();
        foreach ($files as $file) {
            $bundle->add($file);
        }
    }

    /**
     * @param array<int,mixed> $files
     */
    public static function script(array $files): string
    {
    }
}
