<?php

namespace NodiLabs\NodiShell\Abstracts;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;

abstract class BaseSystemCheck implements SystemCheckInterface
{
    protected string $label = '';

    protected string $description = '';

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Run the system check and return results.
     * This method should be implemented by child classes.
     */
    abstract public function run(): array;

    /**
     * Helper method to create a successful check result.
     */
    protected function success(string $message): CheckResultData
    {
        return new CheckResultData(
            successful: true,
            message: $message
        );
    }

    /**
     * Helper method to create a failed check result.
     */
    protected function failure(string $message): CheckResultData
    {
        return new CheckResultData(
            successful: false,
            message: $message
        );
    }

    /**
     * Helper method to create a check result based on a condition.
     */
    protected function check(bool $condition, string $successMessage, string $failureMessage): CheckResultData
    {
        return $condition
            ? $this->success($successMessage)
            : $this->failure($failureMessage);
    }

    /**
     * Helper method to wrap multiple checks in a try-catch block.
     */
    protected function safeCheck(callable $checkFunction): array
    {
        try {
            return $checkFunction();
        } catch (\Exception $e) {
            return [$this->failure($this->getLabel().': FAILED - '.$e->getMessage())];
        }
    }

    /**
     * Helper method to check if a service is running.
     */
    protected function isServiceRunning(string $serviceName): bool
    {
        // Basic service check - can be extended based on needs
        return true; // Placeholder - implement based on your system
    }

    /**
     * Helper method to check if a file exists and is readable.
     */
    protected function isFileAccessible(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Helper method to check if a directory exists and is writable.
     */
    protected function isDirectoryWritable(string $directoryPath): bool
    {
        return is_dir($directoryPath) && is_writable($directoryPath);
    }

    /**
     * Helper method to check database connectivity.
     */
    protected function isDatabaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper method to check if a cache store is working.
     */
    protected function isCacheWorking(?string $store = null): bool
    {
        try {
            $cache = $store ? Cache::store($store) : Cache::store();
            $testKey = 'nodishell_cache_test_'.time();
            $testValue = 'test_value';

            $cache->put($testKey, $testValue, 60);
            $retrieved = $cache->get($testKey);
            $cache->forget($testKey);

            return $retrieved === $testValue;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper method to check if a configuration value is set.
     */
    protected function isConfigSet(string $key): bool
    {
        $value = \config($key);

        return ! is_null($value) && $value !== '';
    }

    /**
     * Helper method to check disk space.
     */
    protected function checkDiskSpace(string $path = '/', int $minSpaceGB = 1): bool
    {
        try {
            $freeSpace = disk_free_space($path);
            $requiredSpace = $minSpaceGB * 1024 * 1024 * 1024; // Convert GB to bytes

            return $freeSpace >= $requiredSpace;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper method to format bytes into human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
