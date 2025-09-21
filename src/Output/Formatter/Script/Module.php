<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Output\Formatter\Script;

use Ronanchilvers\Bundler\Output\Formatter\Script as BaseScript;
use Ronanchilvers\Bundler\Path\Bundle;

class Module extends BaseScript
{
    public function render(Bundle $bundle): Bundle
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

        return $bundle;
    }
}
