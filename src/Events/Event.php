<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * Generic Event value object passed to listeners.
 *
 * Features:
 * - Named event (string identifier)
 * - Mutable payload array (key/value data)
 * - Ability to stop further listener propagation
 *
 * Design notes:
 * - Payload is intentionally untyped (array) for flexibility; callers can
 *   enforce structure via specialised event subclasses if desired.
 * - Listeners can mutate payload via set(), allowing cooperative modification
 *   (e.g. rewriting a path before it is finalised).
 * - Stopping an event (stop()) signals the dispatcher to cease invoking
 *   subsequent listeners. Callers should check isStopped() if they need to
 *   branch on cancellation.
 */
class Event
{
    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(
        protected string $name,
        protected array $payload = []
    ) {
    }

    /** @var bool */
    protected bool $stopped = false;

    /**
     * Get the event name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the entire payload array (by value).
     *
     * @return array<string,mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * Retrieve a payload item by key.
     *
     * @template T
     * @param string $key
     * @param T|null $default
     * @return mixed|T|null
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /**
     * Set or overwrite a payload key.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set(string $key, mixed $value): self
    {
        $this->payload[$key] = $value;
        return $this;
    }

    /**
     * Mark the event as stopped (cancel further listener propagation).
     *
     * @return $this
     */
    public function stop(): self
    {
        $this->stopped = true;
        return $this;
    }

    /**
     * Determine if the event has been stopped.
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }
}
