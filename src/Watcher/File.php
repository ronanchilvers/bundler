<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Watcher;

use Closure;
use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\EventNames;

/**
 * Simple polling-based file watcher.
 *
 * Features:
 *  - Register any number of files to watch (addFile)
 *  - Poll at a configurable microsecond interval
 *  - Emit events for lifecycle + file modifications:
 *        EventNames::WATCHER_START
 *        EventNames::WATCHER_FILE_MODIFIED
 *        EventNames::WATCHER_STOP
 *  - Provide a callback-based API; returning false from the callback stops
 *    the watcher gracefully (reason = "callback").
 *  - External stop() method allows cooperative termination (reason = "stop").
 *
 * NOTE:
 *  This is a polling implementation; for large file sets or low latency
 *  requirements consider an OS-level notification mechanism in future.
 */
class File
{
    /**
     * @var array<string,int> Map of filename => last known mtime
     */
    protected array $files = [];

    /**
     * Indicates whether the watcher loop is currently running.
     */
    protected bool $running = false;

    public function __construct(
        private Dispatcher $events,
        private int $interval = 250, // microseconds
    ) {
    }

    /**
     * Add a file to the watch list.
     *
     * @param string $filename
     * @throws \InvalidArgumentException if the file is not readable.
     */
    public function addFile(string $filename): static
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(
                sprintf('Cannot read file %s', $filename),
            );
        }
        $mtime = filemtime($filename);
        if ($mtime === false) {
            throw new \RuntimeException(
                sprintf('Could not determine modification time for %s', $filename),
            );
        }
        $this->files[$filename] = $mtime;

        return $this;
    }

    /**
     * Determine if the watcher has been started and not yet stopped.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Start the watcher loop.
     *
     * Callback signature:
     *    function (string $filename, int $previousMtime, int $currentMtime): (bool|null)
     *
     * Return false from the callback to request termination. Any other
     * return value (or none) continues watching.
     *
     * Events emitted:
     *  - WATCHER_START (once, before loop)
     *      payload: watcher (this)
     *  - WATCHER_FILE_MODIFIED (per changed file)
     *      payload: watcher, file, previous_mtime, current_mtime
     *  - WATCHER_STOP (once, after loop terminates)
     *      payload: watcher, reason ("callback"|"stop"), file? (if due to callback)
     *
     * @param Closure $onModify Callback fn(string $filename, int $previousMtime, int $currentMtime): bool|null (return false to stop)
     * @return bool true if stopped via stop(), false if callback requested termination
     */
    public function start(Closure $onModify): bool
    {
        if ($this->running) {
            // Already running; no-op.
            return true;
        }
        $this->running = true;

        $this->events->emit(EventNames::WATCHER_START, [
            'watcher' => $this,
        ]);

        $stopReason = null;
        $stopFile = null;

        while ($this->running) {
            clearstatcache();
            foreach ($this->files as $filename => $previousMtime) {
                $currentMtime = file_exists($filename) ? filemtime($filename) : false;
                if ($currentMtime === false) {
                    // Treat disappearance or unreadable file as a "change"
                    $currentMtime = time();
                }
                if ($currentMtime !== $previousMtime) {
                    // Emit modification event first so listeners can react / mutate
                    $this->events->emit(EventNames::WATCHER_FILE_MODIFIED, [
                        $this,
                        $filename,
                        $previousMtime,
                        $currentMtime,
                    ]);

                    // Invoke user callback
                    $result = $onModify($filename, $previousMtime, $currentMtime);
                    if ($result === false) {
                        $stopReason = 'callback';
                        $stopFile = $filename;
                        $this->running = false;
                        break;
                    }
                    // Update cached mtime after callback
                    $this->files[$filename] = (int)$currentMtime;
                }
            }
            if ($this->running) {
                usleep($this->interval);
            }
        }

        if ($stopReason === null) {
            $stopReason = 'stop';
        }

        $payload = [
            'watcher' => $this,
            'reason'  => $stopReason,
        ];
        if ($stopFile !== null) {
            $payload['file'] = $stopFile;
        }

        $this->events->emit(EventNames::WATCHER_STOP, $payload);

        return $stopReason === 'stop';
    }

    /**
     * Request that the watcher loop stop at the next opportunity.
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * Return a snapshot list of currently watched file paths.
     *
     * @return array<int,string>
     */
    public function files(): array
    {
        return array_keys($this->files);
    }
}
