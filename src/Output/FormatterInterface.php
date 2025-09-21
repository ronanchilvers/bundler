<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output;

use Ronanchilvers\Bundler\Path\Bundle;

interface FormatterInterface
{
    /**
     * @param array<int,mixed> $paths
     */
    public function render(Bundle $paths): string;

    /**
     * Decorate this formatter with a decorator class.
     *
     * Implementations typically provided via a trait (e.g. DecorateTrait).
     *
     * @param class-string<FormatterInterface> $class
     * @param array<int|string,mixed> $config
     * @return FormatterInterface
     */
    public function decorate(string $class, array $config = []): FormatterInterface;
}
