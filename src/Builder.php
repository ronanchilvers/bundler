<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Output\Formatter;
use Ronanchilvers\Bundler\Output\FormatterInterface;
use Ronanchilvers\Bundler\Path\Bundle;
use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\EventNames;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Builder
{
    public static function fromYamlFile(
        string $yamlFile,
        ?Dispatcher $events = null,
    ): static {
        $instance = new static($events);
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

        foreach ($settings["bundles"] as $name => $bundleDef) {
            $events?->emit(EventNames::CONFIG_BUNDLE_START, [
                $name,
                $bundleDef,
            ]);
            $decorators = array_merge(
                $globalDecorators,
                @$bundleDef["decorators"] ?: [],
            );
            if (!isset($bundleDef["formatter"])) {
                throw new \InvalidArgumentException(
                    'No formatter specified for bundle \'' . $name . '\'',
                );
            }
            $formatter = Formatter::factory($bundleDef["formatter"]);
            foreach ($decorators as $class => $dSettings) {
                $config = array_merge($globalSettings, $dSettings ?: []);
                $formatter = $formatter->decorate($class, $config);
            }
            $pathBundle = new Bundle([], $name);
            foreach ($bundleDef["paths"] as $p) {
                $realPath = realpath($globalSettings['source'] . DIRECTORY_SEPARATOR . $p);
                if (!file_exists($realPath)) {
                    throw new RuntimeException("Path does not exist - " . $p);
                }
                $events?->emit(EventNames::CONFIG_FILE_ADDING, [
                    $realPath,
                    $p
                ]);
                $pathBundle->add($p);
                $events?->emit(EventNames::CONFIG_FILE_ADDED, [
                    $realPath,
                    $p
                ]);
            }
            $instance->addBundle($name, $formatter, $pathBundle);
            $events?->emit(EventNames::CONFIG_BUNDLE_END, [
                $name,
                $pathBundle,
                $formatter,
            ]);
        }

        return $instance;
    }

    protected $bundles = [];

    public function __construct(protected ?Dispatcher $events = null)
    {
    }

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
