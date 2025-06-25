<?php

namespace NodiLabs\NodiShell\Services;

use Illuminate\Support\Collection;
use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;

class ScriptDiscoveryService
{
    /**
     * @var Collection<int, ScriptInterface>|null
     */
    private ?Collection $scripts = null;

    public function __construct(
        private readonly CategoryDiscoveryService $CategoryDiscoveryService
    ) {}

    /**
     * @return Collection<int, ScriptInterface>
     */
    public function search(string $query): Collection
    {
        $scripts = $this->getScripts();

        return $scripts->filter(function (ScriptInterface $script) use ($query) {
            return str_contains($script->getName(), $query);
        })->values();
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

        /** @var Collection<int, ScriptInterface> $flattenedScripts */
        $flattenedScripts = collect($categories)->flatMap(function (CategoryInterface $category) {
            return $category->getScripts();
        })->values();

        $this->scripts = $flattenedScripts;

        return $this->scripts;
    }

    public function findById(string $id): ?ScriptInterface
    {
        $scripts = $this->getScripts();

        return $scripts->first(fn (ScriptInterface $script) => $script->getName() === $id);
    }
}
