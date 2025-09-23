<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\Event;
use Ronanchilvers\Bundler\Events\EventNames;
use Ronanchilvers\Bundler\Path\Bundle;
use Ronanchilvers\Bundler\Output\FormatterInterface;

/**
 * Orchestrates the processing (rendering) of bundles produced by Builder.
 *
 * Responsibilities:
 *  - Iterate all bundles registered in a Builder
 *  - Emit before / after lifecycle events for each bundle
 *  - Emit error event if an exception occurs (then rethrow by default)
 *  - Add final (possibly decorated) bundles to a Manifest
 *
 * Event payload keys:
 *  - name       : string                (logical bundle name)
 *  - bundle     : Ronanchilvers\Bundler\Path\Bundle (current / rendered bundle)
 *  - formatter  : Ronanchilvers\Bundler\Output\FormatterInterface
 *  - error      : \Throwable (only for BUNDLE_PROCESS_ERROR)
 */
class Processor
{
    public function __construct(
        protected Dispatcher $events
    ) {
    }

    /**
     * Process (render) all bundles in the provided Builder.
     *
     * @param Builder       $builder
     * @param Manifest|null $manifest  Optional existing Manifest to append to
     * @param bool          $rethrow   Whether to rethrow exceptions after
     *                                 emitting the error event
     */
    public function run(Builder $builder, ?Manifest $manifest = null): Manifest
    {
        $manifest = $manifest ?? new Manifest();

        foreach ($builder->bundles() as $name => $data) {
            /** @var FormatterInterface $formatter */
            $formatter = $data['formatter'];
            /** @var Bundle $bundle */
            $bundle = $data['bundle'];

            // Emit BEFORE event
            $this->events->emit(
                EventNames::BUNDLE_PROCESS_BEFORE,
                [
                    $name,
                    $bundle,
                    $formatter,
                ]
            );

            try {
                $rendered = $formatter->render($bundle);

                // Emit AFTER event (with rendered bundle)
                $this->events->emit(
                    EventNames::BUNDLE_PROCESS_AFTER,
                    [
                        $name,
                        $rendered,
                        $formatter,
                    ]
                );

                $manifest->add($name, $rendered);
            } catch (\Throwable $e) {
                // Emit ERROR event
                $this->events->emit(
                    EventNames::BUNDLE_PROCESS_ERROR,
                    [
                        $name,
                        $bundle,
                        $formatter,
                        $e,
                    ]
                );

                throw $e;
            }
        }

        return $manifest;
    }
}
