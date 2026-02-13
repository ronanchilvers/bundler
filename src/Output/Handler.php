<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output;

use Ronanchilvers\Bundler\Format\Traits\ConfigureTrait;
use Ronanchilvers\Bundler\Path\Bundle;

abstract class Handler implements HandlerInterface
{
    use ConfigureTrait;

    public static function factory(string $type, array $config): HandlerInterface
    {
        $type = explode('\\', $type);
        $type = array_map('ucfirst', $type);
        $class = static::class . '\\' . implode('\\', $type);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Unknown handler type $type");
        };
        $handler = new $class();
        foreach ($config as $key => $value) {
            $handler->setConfig($key, $value);
        }

        return $handler;
    }

    abstract public function process(Bundle $bundle): Bundle;
}
