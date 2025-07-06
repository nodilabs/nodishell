<?php

namespace NodiLabs\NodiShell\Examples;

use NodiLabs\NodiShell\Abstracts\BaseSystemCheck;

/**
 * Example system check demonstrating BaseSystemCheck usage.
 * This file shows how to extend BaseSystemCheck with common functionality.
 */
final class ExampleSystemCheck extends BaseSystemCheck
{
    protected string $label = 'Example System Check';

    protected string $description = 'An example system check demonstrating BaseSystemCheck functionality';

    public function run(): array
    {
        return $this->safeCheck(function () {
            $results = [];

            // Example 1: Check if configuration is set
            $appKeySet = $this->isConfigSet('app.key');
            $results[] = $this->check(
                condition: $appKeySet,
                successMessage: 'Application key is configured',
                failureMessage: 'Application key is not set'
            );

            // Example 2: Check database connectivity
            $dbConnected = $this->isDatabaseConnected();
            $results[] = $this->check(
                condition: $dbConnected,
                successMessage: 'Database connection is working',
                failureMessage: 'Database connection failed'
            );

            // Example 3: Check cache functionality
            $cacheWorking = $this->isCacheWorking();
            $results[] = $this->check(
                condition: $cacheWorking,
                successMessage: 'Cache is working properly',
                failureMessage: 'Cache is not working'
            );

            // Example 4: Check file permissions
            $logDir = storage_path('logs');
            $logDirWritable = $this->isDirectoryWritable($logDir);
            $results[] = $this->check(
                condition: $logDirWritable,
                successMessage: 'Log directory is writable',
                failureMessage: 'Log directory is not writable: '.$logDir
            );

            // Example 5: Check disk space
            $diskSpaceOk = $this->checkDiskSpace('/', 1); // At least 1GB free
            $freeSpaceBytes = disk_free_space('/');
            $freeSpace = $freeSpaceBytes !== false ? $this->formatBytes((int) $freeSpaceBytes) : 'unknown';
            $results[] = $this->check(
                condition: $diskSpaceOk,
                successMessage: "Disk space is sufficient ({$freeSpace} free)",
                failureMessage: "Low disk space warning ({$freeSpace} free)"
            );

            return $results;
        });
    }
}
