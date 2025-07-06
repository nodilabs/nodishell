<?php

namespace NodiLabs\NodiShell\Contracts;

interface ScriptInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getTags(): array;

    public function getCategory(): string;

    public function isProductionSafe(): bool;

    public function execute(array $parameters): mixed;

    public function getParameters(): array;
}
