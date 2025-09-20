<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Element;

use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Output\Traits\DecorateTrait;

abstract class Element implements FormatterInterface
{
    use DecorateTrait;

    abstract public function render(array $paths): string;
}
