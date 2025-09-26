<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Format\Formatter;
use Ronanchilvers\Bundler\Format\FormatterInterface;
use Ronanchilvers\Bundler\Output\Handler;
use Ronanchilvers\Bundler\Output\HandlerInterface;
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
                $path = Path::placeholders($item);
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
        $globalHandler = @$settings["handler"] ?: [];
        foreach ($settings["bundles"] as $name => $bundleDefinition) {
            $events?->emit(EventNames::CONFIG_BUNDLE_START, [
                $name,
                $bundleDefinition,
            ]);
            $decoratorSettings = array_merge(
                $globalDecorators,
                @$bundleDefinition["decorators"] ?: [],
            );

            // Build the formatter
            if (!isset($bundleDefinition["formatter"])) {
                throw new \InvalidArgumentException(
                    'No formatter specified for bundle \'' . $name . '\'',
                );
            }
            $formatter = Formatter::factory($bundleDefinition["formatter"]);
            foreach ($decoratorSettings as $type => $dSettings) {
                $config = array_merge($globalSettings, $dSettings ?: []);
                $formatter = Formatter::decorate($type, $formatter, $config);
            }

            // Build the output handler
            $handlerSettings = array_merge(
                $globalHandler,
                @$bundleDefinition["handler"] ?: [],
            );
            $handler = Handler::factory($handlerSettings["type"], $handlerSettings);

            $pathBundle = new Bundle(
                events: $events,
            );
            foreach ($bundleDefinition["paths"] as $p) {
                $realPath = realpath($globalSettings['source'] . DIRECTORY_SEPARATOR . $p);
                if (!file_exists($realPath)) {
                    throw new RuntimeException("Path does not exist - " . $p);
                }
                $pathBundle->add($realPath);
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

    protected Dispatcher $events;
    protected array $bundles;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events ?? new Dispatcher();
        $this->bundles = [];
    }

    public function addBundle(
        string $name,
        FormatterInterface $formatter,
        Bundle $bundle,
        HandlerInterface $handler,
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
