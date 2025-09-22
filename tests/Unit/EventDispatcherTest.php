<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Events\Dispatcher;

#[CoversClass(Dispatcher::class)]
final class EventDispatcherTest extends TestCase
{
    public function testEmitWithoutListenersDoesNotError(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->emit('nothing.here', ['a', 'b']); // should not throw
        $this->assertFalse($dispatcher->hasListeners('nothing.here'));
        $this->assertTrue(true); // explicit assertion to mark the test
    }

    public function testListenerReceivesArgumentsInOrder(): void
    {
        $dispatcher = new Dispatcher();
        $received = null;

        $dispatcher->on('greet', function (string $first, string $second) use (&$received) {
            $received = [$first, $second];
        });

        $dispatcher->emit('greet', ['Hello', 'World']);

        $this->assertSame(['Hello', 'World'], $received);
    }

    public function testMultipleListenersAllInvoked(): void
    {
        $dispatcher = new Dispatcher();
        $calls = [];

        $dispatcher->on('multi', function () use (&$calls) {
            $calls[] = 'first';
        });
        $dispatcher->on('multi', function () use (&$calls) {
            $calls[] = 'second';
        });
        $dispatcher->on('multi', function () use (&$calls) {
            $calls[] = 'third';
        });

        $dispatcher->emit('multi');

        $this->assertSame(['first', 'second', 'third'], $calls);
    }

    public function testHasListenersReflectsRegistration(): void
    {
        $dispatcher = new Dispatcher();
        $this->assertFalse($dispatcher->hasListeners('evt'));

        $dispatcher->on('evt', static function () {
        });

        $this->assertTrue($dispatcher->hasListeners('evt'));
    }

    public function testOffRemovesSpecificListener(): void
    {
        $dispatcher = new Dispatcher();
        $calls = [];

        $a = function () use (&$calls) {
            $calls[] = 'a';
        };
        $b = function () use (&$calls) {
            $calls[] = 'b';
        };

        $dispatcher->on('evt', $a);
        $dispatcher->on('evt', $b);

        $dispatcher->emit('evt');
        $this->assertSame(['a', 'b'], $calls);

        $dispatcher->off('evt', $a);
        $calls = [];

        $dispatcher->emit('evt');
        $this->assertSame(['b'], $calls);
        $this->assertTrue($dispatcher->hasListeners('evt'));
    }

    public function testOffWithoutListenerRemovesAll(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->on('evt', static function () {
        });
        $dispatcher->on('evt', static function () {
        });

        $this->assertTrue($dispatcher->hasListeners('evt'));
        $dispatcher->off('evt');
        $this->assertFalse($dispatcher->hasListeners('evt'));
    }

    public function testClearRemovesAllEvents(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->on('a', static function () {
        });
        $dispatcher->on('b', static function () {
        });

        $this->assertTrue($dispatcher->hasListeners('a'));
        $this->assertTrue($dispatcher->hasListeners('b'));

        $dispatcher->clear();

        $this->assertFalse($dispatcher->hasListeners('a'));
        $this->assertFalse($dispatcher->hasListeners('b'));
    }

    public function testListenersCanReceiveManyArguments(): void
    {
        $dispatcher = new Dispatcher();
        $received = [];

        $dispatcher->on('args', function (...$all) use (&$received) {
            $received = $all;
        });

        $dispatcher->emit('args', ['one', 2, 3.0, ['four'], (object)['five' => 5]]);

        $this->assertCount(5, $received);
        $this->assertSame('one', $received[0]);
        $this->assertSame(2, $received[1]);
        $this->assertSame(3.0, $received[2]);
        $this->assertSame(['four'], $received[3]);
        $this->assertIsObject($received[4]);
        $this->assertObjectHasProperty('five', $received[4]);
    }
}
