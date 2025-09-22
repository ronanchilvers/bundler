<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * Minimal event dispatcher supporting both legacy variadic listener calls
 * via emit() and richer Event objects via dispatch(Event).
 *
 * New behaviour:
 *  - emit(string, array $payload) wraps payload into an Event internally
 *    (backwards compatible with existing tests / listeners expecting raw args)
 *  - dispatch(Event $event) passes a single Event instance to each listener
 *  - Listeners can stop propagation by calling $event->stop()
 */
final class Dispatcher
{
    /**
     * @var array<string, array<int, callable>>
     */
    private array $listeners = [];

    /**
     * Register a listener for an event name.
     *
     * Listener signature options:
     *  - function (Event $event): void
     *  - function (...$args): void   (legacy emit style; receives expanded payload values)
     */
    public function on(string $eventName, callable $listener): self
    {
        $this->listeners[$eventName][] = $listener;
        return $this;
    }

    /**
     * Remove one or all listeners for an event.
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
        foreach ($this->listeners[$eventName] as $i => $registered) {
            if ($registered === $listener) {
                unset($this->listeners[$eventName][$i]);
            }
        }
        if (!$this->listeners[$eventName]) {
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
     * Backwards compatible emit: accepts a payload array (ordered values)
     * which will be provided to legacy listeners as variadic arguments,
     * and also made available to Event listeners via $event->payload().
     *
     * NOTE: The payload is treated as a numeric list here; if you need
     * named / associative data, prefer creating a tailored Event subclass
     * or placing a keyed array inside a single payload element.
     */
    public function emit(string $eventName, array $payload = []): void
    {
        // We create an Event whose payload is keyed numerically so that
        // dispatch() can still give the full Event object to modern listeners.
        $event = new Event($eventName, $payload);
        $this->dispatch($event);
    }

    /**
     * Dispatch an Event object to all listeners registered for its name.
     * Propagation stops if a listener calls $event->stop().
     */
    public function dispatch(Event $event): Event
    {
        foreach ($this->listeners($event->name()) as $listener) {
            // Heuristic: if listener expects 1 parameter we pass Event;
            // otherwise we expand numeric payload values (legacy style).
            $ref = is_array($listener)
                ? new \ReflectionMethod($listener[0], $listener[1])
                : (is_object($listener) && !$listener instanceof \Closure
                    ? new \ReflectionMethod($listener, "__invoke")
                    : new \ReflectionFunction($listener));

            if ($ref->getNumberOfParameters() === 1) {
                $listener($event);
            } else {
                $listener(...$event->payload());
            }

            if ($event->isStopped()) {
                break;
            }
        }
        return $event;
    }

    /**
     * Remove all listeners.
     */
    public function clear(): self
    {
        $this->listeners = [];
        return $this;
    }

    /**
     * Get listeners for a specific event name.
     *
     * @return array<int, callable>
     */
    protected function listeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
