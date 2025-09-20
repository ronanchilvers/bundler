<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Decorator;

use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Output\Traits\DecorateTrait;

abstract class Decorator implements FormatterInterface
{
    use DecorateTrait;

    private FormatterInterface $inner;

    public function __construct(FormatterInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @param array<int,mixed> $paths
     */
    public function render(array $paths): string
    {
        $paths = $this->modifyPaths($paths);

        return $this->inner->render($paths);
    }

    /**
     * @param array<int,mixed> $paths
     * @return void
     */
    abstract protected function modifyPaths(array $paths): array;
}
