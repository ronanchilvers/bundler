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
     * Render the bundle after giving this decorator a chance
     * to adjust paths or attributes. Returns the (possibly
     * modified or replaced) Bundle passed down to the inner
     * formatter chain.
     */
    public function render(Bundle $bundle): Bundle
    {
        $bundle = $this->modifyPaths($bundle);

        return $this->inner->render($bundle);
    }

    protected function setup(): void {}

    /**
     * Allow the decorator to adjust the bundle (add / remove
     * paths, set attributes, write derived assets, etc.).
     *
     * Implementations MUST return the Bundle instance that should
     * be passed to the inner formatter. They MAY return a new
     * Bundle object.
     *
     * @param Bundle $bundle
     * @return Bundle
     */
    abstract protected function modifyPaths(Bundle $bundle): Bundle;
}
