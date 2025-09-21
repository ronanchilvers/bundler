<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Tag;

use Ronanchilvers\Bundler\Path\Bundle;

class Script extends Tag
{
    public function render(Bundle $bundle): string
    {
        $tags = [];
        foreach ($bundle as $path) {
            $tag = '<script src="' .
                htmlspecialchars($path) .
                '"';
            $attributeArray = $bundle->attributes($path);
            $attributes = [];
            foreach ($attributeArray as $key => $value) {
                $attributes[] = $key . '="' . htmlspecialchars((string)$value) . '"';
            }
            $tag .= ' ' . implode(" ", $attributes);
            $tag .= '></script>';

            $tags[] = $tag;
        }

        return implode("\n", $tags);
    }
}
