<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format;

use Ronanchilvers\Bundler\Format\FormatterInterface;
use Ronanchilvers\Bundler\Format\Traits\DecorateTrait;
use Ronanchilvers\Bundler\Path\Bundle;

abstract class Formatter implements FormatterInterface
{
    use DecorateTrait;

    public static function factory(string $type): FormatterInterface
    {
        $type = explode('\\', $type);
        $type = array_map('ucfirst', $type);
        $class = static::class . '\\' . implode('\\', $type);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Unknown formatter type $type");
        };

        return new $class();
    }

    abstract public function render(Bundle $paths): Bundle;
}
