<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Element;

class Stylesheet extends Element
{
    public function render(array $paths): string
    {
        $tags = [];
        foreach ($paths as $path) {
            $tags[] = '<link rel="stylesheet" href="' . htmlspecialchars($path) . '">';
        }

        return implode("\n", $tags);
    }
}
