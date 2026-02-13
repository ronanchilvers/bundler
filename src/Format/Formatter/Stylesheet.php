<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Formatter;

use Ronanchilvers\Bundler\Manifest;
use Ronanchilvers\Bundler\Format\Formatter;
use Ronanchilvers\Bundler\Path\Bundle;

class Stylesheet extends Formatter
{
    public function render(Bundle $bundle): Bundle
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

        return $bundle;
    }
}
