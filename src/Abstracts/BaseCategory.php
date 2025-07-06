<?php

namespace NodiLabs\NodiShell\Abstracts;

use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;

abstract class BaseCategory implements CategoryInterface
{
    protected string $name = '';

    protected string $description = '';

    protected string $icon = '📁';

    protected string $color = 'blue';

    protected int $sortOrder = 100;

    protected bool $enabled = true;

    protected array $scripts = [];

    protected bool $scriptsLoaded = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getScripts(): array
    {
        if (! $this->scriptsLoaded) {
            $this->loadScripts();
            $this->scriptsLoaded = true;
        }

        return $this->scripts;
    }

    /**
     * Load scripts for this category.
     * This method should be implemented by child classes.
     */
    abstract protected function loadScripts(): void;

    /**
     * Helper method to add a script to the category.
     */
    protected function addScript(ScriptInterface $script): void
    {
        $this->scripts[] = $script;
    }

    /**
     * Helper method to add multiple scripts to the category.
     */
    protected function addScripts(array $scripts): void
    {
        foreach ($scripts as $script) {
            if ($script instanceof ScriptInterface) {
                $this->addScript($script);
            }
        }
    }

    /**
     * Helper method to remove a script by name.
     */
    protected function removeScript(string $scriptName): void
    {
        $this->scripts = array_filter($this->scripts, function (ScriptInterface $script) use ($scriptName) {
            return $script->getName() !== $scriptName;
        });
    }

    /**
     * Helper method to get script count.
     */
    public function getScriptCount(): int
    {
        return count($this->getScripts());
    }

    /**
     * Helper method to check if category has scripts.
     */
    public function hasScripts(): bool
    {
        return $this->getScriptCount() > 0;
    }

    /**
     * Helper method to get scripts by tag.
     */
    public function getScriptsByTag(string $tag): array
    {
        return array_filter($this->getScripts(), function (ScriptInterface $script) use ($tag) {
            return in_array($tag, $script->getTags());
        });
    }

    /**
     * Helper method to get production safe scripts only.
     */
    public function getProductionSafeScripts(): array
    {
        return array_filter($this->getScripts(), function (ScriptInterface $script) {
            return $script->isProductionSafe();
        });
    }

    /**
     * Helper method to find a script by name.
     */
    public function findScript(string $scriptName): ?ScriptInterface
    {
        foreach ($this->getScripts() as $script) {
            if ($script->getName() === $scriptName) {
                return $script;
            }
        }

        return null;
    }

    /**
     * Helper method to search scripts by name or description.
     */
    public function searchScripts(string $query): array
    {
        $query = strtolower($query);

        return array_filter($this->getScripts(), function (ScriptInterface $script) use ($query) {
            return str_contains(strtolower($script->getName()), $query) ||
                   str_contains(strtolower($script->getDescription()), $query);
        });
    }
}
