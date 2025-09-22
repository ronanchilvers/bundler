<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Decorator;

use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Output\Traits\DecorateTrait;
use Ronanchilvers\Bundler\Path\Bundle;

abstract class Decorator implements FormatterInterface
{
    use DecorateTrait;

    private FormatterInterface $inner;

    public function __construct(FormatterInterface $inner)
    {
        $this->inner = $inner;
        $this->setup();
    }

    /**
     * @param array<int,mixed> $paths
     */
    public function render(Bundle $bundle): Bundle
    {
        $bundle = $this->modifyPaths($bundle);

        return $this->inner->render($bundle);
    }

    protected function setup(): void
    {
    }

    /**
     * @param array<int,mixed> $paths
     * @return void
     */
    abstract protected function modifyPaths(Bundle $bundle): Bundle;
}
