<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format;

use Ronanchilvers\Bundler\Path\Bundle;

interface FormatterInterface
{
    /**
     * Render the provided bundle and return a (possibly modified) bundle.
     *
     * @param Bundle $bundle
     */
    public function render(Bundle $bundle): Bundle;
}
