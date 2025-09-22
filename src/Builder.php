<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Output\Formatter;
use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Path\Bundle;
use Symfony\Component\Yaml\Yaml;

class Builder
{
    public static function fromYamlFile(string $yamlFile): static
    {
        $instance = new static();
        $settings = Yaml::parseFile($yamlFile);

        $dirname = realpath(dirname($yamlFile));
        $globalSettings = array_map(
            function ($item) use ($dirname) {
                if (!is_string($item)) {
                    return $item;
                }
                if (false === stripos($item, "__DIR__")) {
                    return $item;
                }
                $path = str_replace("__DIR__", $dirname, $item);
                if (!is_dir($path)) {
                    throw new \InvalidArgumentException(
                        "Path does not exist: " . $path,
                    );
                }

                return realpath($path);
            },
            $settings["globals"] ?: [],
        );
        $globalDecorators = @$settings["decorators"] ?: [];

        foreach ($settings["bundles"] as $name => $bundle) {
            $decorators = array_merge(
                $globalDecorators,
                @$bundle["decorators"] ?: [],
            );
            if (!isset($bundle["formatter"])) {
                throw new \InvalidArgumentException(
                    'No formatter specified for bundle \'' . $name . '\'',
                );
            }
            $formatter = Formatter::factory($bundle["formatter"]);
            foreach ($decorators as $class => $settings) {
                $config = array_merge($globalSettings, $settings ?: []);
                $formatter = $formatter->decorate($class, $config);
            }
            $pathBundle = new Bundle();
            $pathBundle->addMany($bundle["paths"]);
            $instance->addBundle($name, $formatter, $pathBundle);
        }

        return $instance;
    }

    protected $bundles = [];

    public function __construct() {}

    public function addBundle(
        string $name,
        FormatterInterface $formatter,
        Bundle $bundle,
    ): static {
        $this->bundles[$name] = [
            "formatter" => $formatter,
            "bundle" => $bundle,
        ];

        return $this;
    }

    public function bundles(): array
    {
        return $this->bundles;
    }
}
