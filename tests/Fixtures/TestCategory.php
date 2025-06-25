<?php

namespace NodiLabs\NodiShell\Tests\Fixtures;

use NodiLabs\NodiShell\Contracts\CategoryInterface;

class TestCategory implements CategoryInterface
{
    public function __construct(
        private string $name = 'Test Category',
        private string $description = 'A test category',
        private string $icon = '🧪',
        private int $sortOrder = 1,
        private bool $enabled = true,
        private array $scripts = []
    ) {}

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
        return $this->scripts;
    }

    public function setScripts(array $scripts): self
    {
        $this->scripts = $scripts;
        return $this;
    }
}
