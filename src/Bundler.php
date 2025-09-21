<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Output\Tag\Script;
use Ronanchilvers\Bundler\Output\Tag\Stylesheet;
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

    public static function module(): FormatterInterface
    {
        return static::createFormatter(Module::class);
    }

    protected static function createFormatter(string $class): FormatterInterface
    {
        return new $class();
    }
}
