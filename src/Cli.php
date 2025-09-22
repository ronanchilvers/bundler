<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class Cli
{
    private const LEVEL_INFO = "info";
    private const LEVEL_NOTICE = "notice";
    private const LEVEL_ERROR = "error";

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

    protected static function write(string $level, string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        $prefix = match ($level) {
            self::LEVEL_INFO => "\033[32m{$timestamp} INFO\033[0m",
            self::LEVEL_NOTICE => "\033[34m{$timestamp} NOTICE\033[0m",
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
