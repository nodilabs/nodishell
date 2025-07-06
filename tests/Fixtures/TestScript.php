<?php

namespace NodiLabs\NodiShell\Tests\Fixtures;

use NodiLabs\NodiShell\Contracts\ScriptInterface;

class TestScript implements ScriptInterface
{
    public function __construct(
        private string $name = 'test-script',
        private string $description = 'A test script',
        private array $tags = ['test'],
        private string $category = 'test',
        private bool $productionSafe = false,
        private array $parameters = [],
        private mixed $executeResult = ['success' => true]
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function isProductionSafe(): bool
    {
        return $this->productionSafe;
    }

    public function execute(array $parameters): mixed
    {
        return $this->executeResult;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
