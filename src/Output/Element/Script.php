<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Element;

class Script extends Element
{
    public function render(array $paths): string
    {
        $tags = [];
        foreach ($paths as $path) {
            $tags[] = '<script src="' . htmlspecialchars($path) . '"></script>';
        }

        return implode("\n", $tags);
    }
}
