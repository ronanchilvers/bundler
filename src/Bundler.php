<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Output\Element\Script;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Output\FormatterInterface;

class Bundler
{
    /**
     * @param array<int,string> $files
     * @return string
     */
    public static function stylesheet(): FormatterInterface
    {
        return static::createFormatter(Stylesheet::class);
    }

    /**
     * @param array<int,mixed> $files
     */
    public static function script(): FormatterInterface
    {
        return static::createFormatter(Script::class);
    }

    protected static function createFormatter(string $class): FormatterInterface
    {
        return new $class();
    }
}
