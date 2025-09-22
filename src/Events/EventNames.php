<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * Centralised list of event name constants used throughout the Bundler.
 *
 * Grouped by functional area:
 *
 * Configuration / bundle definition lifecycle:
 *  - CONFIG_BUNDLE_START   : Emitted before a bundle definition from YAML is processed
 *  - CONFIG_BUNDLE_END     : Emitted after a bundle definition has been fully constructed
 *  - CONFIG_FILE_ADDING    : Emitted before a file path is added to a Bundle (cancellable / mutable)
 *  - CONFIG_FILE_ADDED     : Emitted after a file path has been added to a Bundle
 *
 * Processing lifecycle (execution / rendering):
 *  - BUNDLE_PROCESS_BEFORE : Emitted immediately before a bundle is rendered by its formatter
 *  - BUNDLE_PROCESS_AFTER  : Emitted after successful rendering
 *  - BUNDLE_PROCESS_ERROR  : Emitted if an exception occurs during processing
 *
 * These constants are intended to avoid typos in event names and provide a
 * single place to discover available events.
 */
final class EventNames
{
    // Configuration / bundle definition events
    public const CONFIG_BUNDLE_START   = 'config.bundle.start';
    public const CONFIG_BUNDLE_END     = 'config.bundle.end';
    public const CONFIG_FILE_ADDING    = 'config.bundle.file.adding';
    public const CONFIG_FILE_ADDED     = 'config.bundle.file.added';

    // Processing / rendering events
    public const BUNDLE_PROCESS_BEFORE = 'bundle.process.before';
    public const BUNDLE_PROCESS_AFTER  = 'bundle.process.after';
    public const BUNDLE_PROCESS_ERROR  = 'bundle.process.error';

    // Watcher
    public const WATCHER_START         = 'watcher.start';
    public const WATCHER_WAKE          = 'watcher.wake';
    public const WATCHER_END           = 'watcher.end';
}
