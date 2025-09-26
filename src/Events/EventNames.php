<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Events;

/**
 * Centralised list of event name constants used throughout the Bundler.
 *
 * GROUPS
 * ======
 * Configuration / bundle definition lifecycle:
 *  - CONFIG_BUNDLE_START    : Before a bundle stanza from YAML is processed
 *  - CONFIG_BUNDLE_END      : After a bundle has been fully constructed
 *  - CONFIG_FILE_ADDING     : Before a file path is added to a Bundle (cancellable / mutable)
 *  - CONFIG_FILE_ADDED      : After a file path has been added to a Bundle
 *
 * Processing (rendering) lifecycle:
 *  - BUNDLE_PROCESS_BEFORE  : Immediately before a bundle is rendered by its formatter
 *  - BUNDLE_PROCESS_AFTER   : After successful rendering
 *  - BUNDLE_PROCESS_ERROR   : An exception occurred during rendering (error in payload)
 *
 * Output handling lifecycle:
 *  - OUTPUT_HANDLE_BEFORE   : Immediately before an output handler is invoked
 *  - OUTPUT_HANDLE_AFTER    : After successful output handling
 *
 * File watcher lifecycle:
 *  - WATCHER_START          : Watcher loop about to begin
 *  - WATCHER_STOP           : Watcher loop is terminating (graceful stop)
 *  - WATCHER_FILE_MODIFIED  : A watched file's mtime changed
 *
 * RATIONALE
 * =========
 * Using constants avoids typo-prone string literals throughout the codebase
 * and provides a discoverable catalogue of supported events.
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

    // Watcher / file system events
    public const WATCHER_START         = 'watcher.start';
    public const WATCHER_STOP          = 'watcher.stop';
    public const WATCHER_FILE_MODIFIED = 'watcher.file.modified';

    // Output handling events
    public const OUTPUT_HANDLE_BEFORE  = 'output.handle.before';
    public const OUTPUT_HANDLE_AFTER   = 'output.handle.after';
}
