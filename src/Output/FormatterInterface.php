<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output;

interface FormatterInterface
{
    /**
     * @param array<int,mixed> $paths
     */
    public function render(array $paths): string;
}
