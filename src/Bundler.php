<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Output\Element\Script;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Output\FormatterInterface;

class Bundler
{
    private static $config = [
        'source_root' => null,
        'destination_root' => null,
        'decorators' => [],
    ];

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
        $formatter = static::decorateFormatter(
            new $class()
        );

        return $formatter;
    }

    protected static function decorateFormatter(FormatterInterface $formatter): FormatterInterface
    {
        foreach (self::$config['decorators'] as $decorator) {
            $formatter = new $decorator($formatter);
        }

        return $formatter;
    }
}
