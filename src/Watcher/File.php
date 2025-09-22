<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Watcher;

use Closure;
use Ronanchilvers\Bundler\Events\Dispatcher;
use Ronanchilvers\Bundler\Events\EventNames;

class File
{
    protected array $files = [];

    public function __construct(
        private Dispatcher $events,
        private int $interval = 250,
    ) {
    }

    public function addFile(string $filename): static
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(
                sprintf("Cannot read file %s", $filename),
            );
        }
        $this->files[$filename] = filemtime($filename);

        return $this;
    }

    public function start(Closure $closure): bool
    {
        $this->events->emit(EventNames::WATCHER_START, [
            'watcher' => $this
        ]);
        while (true) {
            $this->events->emit(EventNames::WATCHER_WAKE, [
                'watcher' => $this
            ]);
            clearstatcache();
            foreach ($this->files as $filename => $lastModified) {
                if (filemtime($filename) !== $lastModified) {
                    $shouldContinue = $closure($filename);
                    if ($shouldContinue === false) {
                        // Allow cooperative termination of watcher
                        return false;
                    }
                }
                $this->files[$filename] = filemtime($filename);
            }
            usleep($this->interval);
        }
        $this->events->emit(EventNames::WATCHER_END, [
            'watcher' => $this
        ]);

        return true;
    }

    protected function configureListeners(): void
    {
        $this->events->on(EventNames::CONFIG_FILE_ADDED, function ($filename) {
            echo "File changed: $filename" . PHP_EOL;
        });
    }
}
