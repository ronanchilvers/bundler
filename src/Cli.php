<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class Cli
{
    public const LEVEL_DEBUG = 8;
    public const LEVEL_INFO = 4;
    public const LEVEL_NOTICE = 2;
    public const LEVEL_ERROR = 1;

    private static $level = 2;

    public static function setLevel(int $level): void
    {
        static::$level = $level;
    }

    public static function debug(string $message): void
    {
        static::write(self::LEVEL_DEBUG, $message);
    }

    public static function info(string $message): void
    {
        static::write(self::LEVEL_INFO, $message);
    }

    public static function notice(string $message): void
    {
        static::write(self::LEVEL_NOTICE, $message);
    }

    public static function error(string $message): void
    {
        static::write(self::LEVEL_ERROR, $message);
    }

    protected static function write(int $level, string $message): void
    {
        if ($level > static::$level) {
            return;
        }
        $timestamp = date("Y-m-d H:i:s");
        $prefix = match ($level) {
            self::LEVEL_DEBUG => "\033[33m{$timestamp} DEBUG\033[0m",
            self::LEVEL_INFO => "\033[34m{$timestamp} INFO\033[0m",
            self::LEVEL_NOTICE => "\033[32m{$timestamp} NOTICE\033[0m",
            self::LEVEL_ERROR => "\033[31m{$timestamp} ERROR\033[0m",
            default => "",
        };
        echo sprintf(
            '%s %s',
            $prefix,
            $message
        ) . PHP_EOL;
    }
}
