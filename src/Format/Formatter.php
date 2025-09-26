<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format;

use Ronanchilvers\Bundler\Format\FormatterInterface;
use Ronanchilvers\Bundler\Format\Traits\ConfigureTrait;
use Ronanchilvers\Bundler\Path\Bundle;

abstract class Formatter implements FormatterInterface
{
    use ConfigureTrait;

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

    public static function decorate(
        string $type,
        FormatterInterface $formatter,
        array $config
    ): FormatterInterface {
        $type = explode('\\', $type);
        $type = implode('\\', array_map('ucfirst', $type));
        $class = str_replace('Formatter', '', static::class) . 'Decorator\\' . $type;
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Unknown formatter type $type");
        };

        $decorator = new $class($formatter);
        foreach ($config as $key => $value) {
            $decorator->setConfig($key, $value);
        }

        return $decorator;
    }

    abstract public function render(Bundle $paths): Bundle;
}
