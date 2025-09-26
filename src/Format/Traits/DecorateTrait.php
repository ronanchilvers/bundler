<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Traits;

use Ronanchilvers\Bundler\Format\FormatterInterface;
use Ronanchilvers\Bundler\Format\Traits\ConfigureTrait;

trait DecorateTrait1
{
    use ConfigureTrait;

    /**
     * @param array<int,mixed> $config
     */
    public function decorate(
        string $class,
        array $config = [],
    ): FormatterInterface {
        $decorator = new $class($this);
        foreach ($config as $key => $value) {
            $decorator->setConfig($key, $value);
        }

        return $decorator;
    }
}
