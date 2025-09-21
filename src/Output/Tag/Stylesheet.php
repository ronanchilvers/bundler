<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Tag;

use Ronanchilvers\Bundler\Path\Bundle;

class Stylesheet extends Tag
{
    public function render(Bundle $bundle): string
    {
        $tags = [];
        foreach ($bundle as $path) {
            $tag = '<link rel="stylesheet" href="' .
                htmlspecialchars($path) .
                '"';
            $attributeArray = $bundle->attributes($path);
            $attributes = [];
            foreach ($attributeArray as $key => $value) {
                $attributes[] = $key . '="' . htmlspecialchars((string)$value) . '"';
            }
            $tag .= ' ' . implode(" ", $attributes);
            $tag .= '>';

            $tags[] = $tag;
        }

        return implode("\n", $tags);
    }
}
