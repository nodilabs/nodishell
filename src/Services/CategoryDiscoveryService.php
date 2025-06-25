<?php

namespace NodiLabs\NodiShell\Services;

use NodiLabs\NodiShell\Contracts\CategoryInterface;

class CategoryDiscoveryService
{
    private array $categories = [];

    private bool $initialized = false;

    /**
     * @return array<string, CategoryInterface>
     */
    public function getAllCategories(): array
    {
        $this->ensureInitialized();

        return $this->categories;
    }

    public function getCategory(string $key): ?CategoryInterface
    {
        $this->ensureInitialized();

        return $this->categories[$key] ?? null;
    }

    public function registerCategory(string $key, CategoryInterface $category): void
    {
        $this->categories[$key] = $category;
    }

    public function getEnabledCategories(): array
    {
        $this->ensureInitialized();

        return array_filter($this->categories, function (CategoryInterface $category) {
            return $category->isEnabled();
        });
    }

    public function getCategoriesSorted(): array
    {
        $this->ensureInitialized();

        $categories = $this->categories;

        uasort($categories, function (CategoryInterface $a, CategoryInterface $b) {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $categories;
    }

    public function getCategoryByScript(string $scriptName): ?CategoryInterface
    {
        $this->ensureInitialized();

        foreach ($this->categories as $category) {
            foreach ($category->getScripts() as $script) {
                if ($script->getName() === $scriptName) {
                    return $category;
                }
            }
        }

        return null;
    }

    private function ensureInitialized(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->discover();
        $this->initialized = true;
    }

    /**
     * Discover and register categories from the defined path.
     */
    public function discover(): void
    {
        $categoryPath = config('nodishell.discovery.categories_path');
        $baseNamespace = 'App\\Console\\NodiShell\\Categories';

        if (! file_exists($categoryPath)) {
            // Or log a warning, depending on desired behavior
            return;
        }

        $files = new \DirectoryIterator($categoryPath);

        foreach ($files as $file) {
            if ($file->isDot() || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $file->getBasename('.php');
            $fqcn = $baseNamespace.'\\'.$className;

            if (class_exists($fqcn)) {
                $reflection = new \ReflectionClass($fqcn);

                if ($reflection->isInstantiable() && $reflection->implementsInterface(CategoryInterface::class)) {
                    $key = strtolower(str_replace('Category', '', $className));
                    $this->registerCategory($key, new $fqcn);
                }
            }
        }
    }
}
