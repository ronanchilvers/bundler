<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Traits;

use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Output\Traits\ConfigureTrait;

trait DecorateTrait
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
