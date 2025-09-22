<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * A very small and straightforward event system.
 *
 * Features:
 * - Register any number of listeners for any string event name
 * - Dispatch events with arbitrary arguments
 * - Arguments are passed directly to the listener as call arguments
 *
 * Basic usage:
 *
 * $dispatcher = new Dispatcher();
 *
 * $dispatcher->on('asset.bundled', function (string $name) {
 *     // ... do something
 * });
 *
 * $event = $dispatcher->dispatch('asset.bundled', [
 *     "Fred"
 * ]);
 */
final class Dispatcher
{
    /**
     * @var array<string, array<int, array<int, callable(Event): mixed>>>
     * Structure: [eventName => [priority => [listener, ...], ...], ...]
     */
    private array $listeners = [];

    /**
     * Register a listener for an event.
     *
     * Higher priority listeners run first. Priority can be negative.
     *
     * @param string   $eventName
     * @param callable $listener  function (Event $event): mixed
     * @return $this
     */
    public function on(string $eventName, callable $listener): self
    {
        $this->listeners[$eventName][] = $listener;
        return $this;
    }

    /**
     * Remove one or all listeners for an event.
     *
     * If $listener is null, all listeners for the event are removed.
     */
    public function off(string $eventName, ?callable $listener = null): self
    {
        if (!isset($this->listeners[$eventName])) {
            return $this;
        }

        if ($listener === null) {
            unset($this->listeners[$eventName]);
            return $this;
        }

        foreach ($this->listeners[$eventName] as $listeners) {
            foreach ($listeners as $index => $registered) {
                if ($registered === $listener) {
                    unset($this->listeners[$eventName][$index]);
                }
            }
        }

        if ($this->listeners[$eventName] === []) {
            unset($this->listeners[$eventName]);
        }

        return $this;
    }

    /**
     * Determine if an event has any listeners.
     */
    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]);
    }

    /**
     * Dispatch an event.
     *
     * @param string $eventName
     * @param array  $payload   Arbitrary associative data for listeners
     * @return Event The dispatched event instance
     */
    public function emit(string $eventName, array $payload = []): void
    {
        foreach ($this->listeners($eventName) as $listener) {
            $result = $listener(...$payload);
        }
    }

    /**
     * Remove all listeners for all events.
     */
    public function clear(): self
    {
        $this->listeners = [];
        return $this;
    }

    /**
     * Get the (sorted) listeners for an event.
     *
     * @return array<int, callable(Event): mixed>
     */
    protected function listeners(string $eventName): array
    {
        if (!$this->hasListeners($eventName)) {
            return [];
        }

        $ordered = [];
        foreach ($this->listeners[$eventName] as $listener) {
            $ordered[] = $listener;
        }

        return $ordered;
    }
}
