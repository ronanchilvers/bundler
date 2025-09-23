<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\Event;
use Ronanchilvers\Bundler\Events\EventNames;
use Ronanchilvers\Bundler\Events\FileAddingEvent;
use Ronanchilvers\Bundler\Path\Bundle;

/**
 * Tests exercising the new Event object dispatch pathway, payload mutation,
 * propagation stopping, and integration with Bundle path addition events.
 */
#[CoversClass(Dispatcher::class)]
#[CoversClass(Event::class)]
final class EventObjectTest extends TestCase
{
    public function testEventObjectPropagationAndMutation(): void
    {
        $dispatcher = new Dispatcher();
        $sequence   = [];
        $finalFoo   = null;

        $dispatcher->on('custom.evt', function (Event $event) use (&$sequence) {
            $sequence[] = 'listener1';
            $this->assertSame('custom.evt', $event->name());
            $this->assertSame('bar', $event->get('foo'));
            // Mutate payload so downstream listeners see change
            $event->set('foo', 'baz');
        });

        $dispatcher->on('custom.evt', function (Event $event) use (&$sequence, &$finalFoo) {
            $sequence[] = 'listener2';
            $finalFoo   = $event->get('foo');
        });

        $dispatcher->emit('custom.evt', ['bar']));

        $this->assertSame(['listener1', 'listener2'], $sequence);
        $this->assertSame('baz', $finalFoo, 'Second listener should observe mutated value');
    }

    public function testEventStopPreventsFurtherListeners(): void
    {
        $dispatcher = new Dispatcher();
        $order      = [];

        $dispatcher->on('stop.test', function (Event $event) use (&$order) {
            $order[] = 'first';
        });

        $dispatcher->on('stop.test', function (Event $event) use (&$order) {
            $order[] = 'second';
            $event->stop(); // Halt propagation
        });

        $dispatcher->on('stop.test', function (Event $event) use (&$order) {
            $order[] = 'third-should-not-run';
        });

        $dispatcher->emit('stop.test'));

        $this->assertSame(['first', 'second'], $order);
        $this->assertNotContains('third-should-not-run', $order);
    }

    public function testBundleFileAddingEventCancellationAndSuccess(): void
    {
        $dispatcher         = new Dispatcher();
        $addedPaths         = [];
        $addingListenerHits = 0;
        $cancelHits         = 0;

        // Listener that cancels a specific unwanted path
        $dispatcher->on(EventNames::CONFIG_FILE_ADDING, function (FileAddingEvent $event) use (&$cancelHits) {
            if ($event->getPath() === '/bad.js') {
                $cancelHits++;
                $event->cancel();
            }
        });

        // Generic listener (should not fire for cancelled events after stop)
        $dispatcher->on(EventNames::CONFIG_FILE_ADDING, function (FileAddingEvent $event) use (&$addingListenerHits) {
            $addingListenerHits++;
            // Example mutation: normalise duplicate slashes
            $event->setPath(preg_replace('#//+#', '/', $event->getPath()) ?? $event->getPath());
        });

        // Track successfully added
        $dispatcher->on(EventNames::CONFIG_FILE_ADDED, function (Event $event) use (&$addedPaths) {
            $addedPaths[] = $event->get('path');
        });

        $bundle = new Bundle([], $dispatcher, 'demo');

        $bundle->add('/good.js');
        $bundle->add('/bad.js');   // should be cancelled
        $bundle->add('//good.css'); // mutation normalises slashes

        $final = $bundle->paths();

        $this->assertContains('/good.js', $final);
        $this->assertContains('/good.css', $final, 'Normalised good.css path expected');
        $this->assertNotContains('/bad.js', $final, 'Cancelled path should not be present');

        // Added events only for non-cancelled
        $this->assertSame(['/good.js', '/good.css'], $addedPaths);

        // First listener hit for all 3 attempts; second (post-cancel) should not run for /bad.js
        $this->assertSame(1, $cancelHits, 'Cancel listener should have fired exactly once');
        $this->assertSame(2, $addingListenerHits, 'Adding listener should not have run for cancelled path');
    }

    public function testLegacyEmitStillWorksAlongsideEventDispatch(): void
    {
        $dispatcher = new Dispatcher();
        $received   = [];

        // Legacy style listener (expects variadics, not an Event)
        $dispatcher->on('legacy.mixed', function ($a, $b) use (&$received) {
            $received[] = $a;
            $received[] = $b;
        });

        // New style listener for the same event
        $dispatcher->on('legacy.mixed', function (Event $event) use (&$received) {
            $received[] = $event->get(0); // numeric payload index
            $received[] = $event->get(1);
        });

        $dispatcher->emit('legacy.mixed', ['x', 'y']);

        $this->assertSame(['x', 'y', 'x', 'y'], $received);
    }
}
