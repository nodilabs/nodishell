<?php

namespace NodiLabs\NodiShell\Services;

use Illuminate\Support\Collection;
use NodiLabs\NodiShell\Contracts\SystemCheckInterface;

final class SystemCheckService
{
    private array $checks = [];

    private bool $initialized = false;

    /**
     * @return Collection<int, SystemCheckInterface>
     */
    public function getChecks(): Collection
    {
        $this->ensureInitialized();

        return collect($this->checks);
    }

    public function register(SystemCheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    private function ensureInitialized(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->loadFromConfig();
        if (config('nodishell.discovery.system_checks_discovery')) {
            $this->discover();
        }
        $this->initialized = true;
    }

    /**
     * Load system checks from configuration array.
     */
    private function loadFromConfig(): void
    {
        $checkClasses = config('nodishell.system_checks', []);

        foreach ($checkClasses as $class) {
            if (class_exists($class)) {
                $this->register(app($class));
            }
        }
    }

    /**
     * Auto-discover system checks from the configured directory.
     */
    private function discover(): void
    {
        $checksPath = config('nodishell.discovery.checks_path');
        $baseNamespace = 'App\\Console\\NodiShell\\Checks';

        if (! $checksPath || ! file_exists($checksPath)) {
            return;
        }

        $files = new \DirectoryIterator($checksPath);

        foreach ($files as $file) {
            if ($file->isDot() || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $file->getBasename('.php');
            $fqcn = $baseNamespace.'\\'.$className;

            if (class_exists($fqcn)) {
                $reflection = new \ReflectionClass($fqcn);

                if ($reflection->isInstantiable() && $reflection->implementsInterface(SystemCheckInterface::class)) {
                    $this->register(new $fqcn);
                }
            }
        }
    }
}
