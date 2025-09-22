<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * Specialised event for when a file path is being added to a Bundle.
 *
 * Semantics:
 * - Dispatched with initial payload:
 *     [
 *         'bundle' => string|null, // logical bundle name (may be null if unknown)
 *         'path'   => string,      // the path about to be added
 *     ]
 * - Listeners MAY:
 *     - Mutate the path (e.g. normalise / rewrite) via setPath()
 *     - Cancel the addition via cancel() (internally stop())
 *
 * Life-cycle:
 * 1. A FileAddingEvent (CONFIG_FILE_ADDING) is dispatched before insertion.
 * 2. If not cancelled, the (possibly mutated) path is added.
 * 3. A generic Event (CONFIG_FILE_ADDED) is emitted after successful insertion.
 */
class FileAddingEvent extends Event
{
    /**
     * Convenience accessor for the bundle (logical) name if provided.
     */
    public function getBundle(): ?string
    {
        return $this->get('bundle');
    }

    /**
     * Get the (current / possibly mutated) path value.
     */
    public function getPath(): string
    {
        return (string)$this->get('path', '');
    }

    /**
     * Set / mutate the path value.
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        return $this->set('path', $path);
    }

    /**
     * Cancel the addition of this path to the bundle.
     *
     * @return $this
     */
    public function cancel(): self
    {
        return $this->stop();
    }

    /**
     * Shortcut helper to test if the event was cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->isStopped();
    }
}
