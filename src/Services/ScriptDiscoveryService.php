<?php

namespace NodiLabs\NodiShell\Services;

use Illuminate\Support\Collection;
use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;

class ScriptDiscoveryService
{
    /**
     * @var Collection<string, ScriptInterface>|null
     */
    private ?Collection $scripts = null;

    public function __construct(
        private readonly CategoryDiscoveryService $CategoryDiscoveryService
    ) {}

    public function search(string $query): Collection
    {
        $scripts = $this->getScripts();

        return $scripts->filter(function (ScriptInterface $script) use ($query) {
            return str_contains($script->getName(), $query);
        });
    }

    /**
     * @return Collection<int, ScriptInterface>
     */
    private function getScripts(): Collection
    {
        if ($this->scripts !== null) {
            return $this->scripts;
        }

        $categories = $this->CategoryDiscoveryService->getAllCategories();

        $this->scripts = collect($categories)->map(function (CategoryInterface $category) {
            return $category->getScripts();
        })->flatten();

        return $this->scripts;
    }

    public function findById(string $id): ?ScriptInterface
    {
        $scripts = $this->getScripts();

        return $scripts->first(fn (ScriptInterface $script) => $script->getName() === $id);
    }
}
