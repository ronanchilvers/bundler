<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output;

use Ronanchilvers\Bundler\Path\Bundle;

interface HandlerInterface
{
    public function process(Bundle $bundle): Bundle;
}
