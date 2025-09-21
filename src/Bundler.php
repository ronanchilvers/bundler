<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Config;
use Ronanchilvers\Bundler\Output\Formatter\Script;
use Ronanchilvers\Bundler\Output\Formatter\Script\Module;
use Ronanchilvers\Bundler\Output\Formatter\Stylesheet;
use Ronanchilvers\Bundler\Output\FormatterInterface;

class Bundler
{
    /**
     * Config store object
     * @var Config|null
     */
    protected static $config = null;

    /**
     * Registry for configured bundlers
     * @var array<string,FormatterInterface>
     */
    protected static $registry = [];

    public static function setConfig(Config $config): void
    {
        static::$config = $config;
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

    public static function module(): FormatterInterface
    {
        return static::createFormatter(Module::class);
    }

    protected static function createFormatter(string $class): FormatterInterface
    {
        return new $class();
    }
}
