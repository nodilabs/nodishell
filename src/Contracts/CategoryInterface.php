<?php

namespace NodiLabs\NodiShell\Contracts;

interface CategoryInterface
{
    public function isEnabled(): bool;

    public function getSortOrder(): int;

    public function getScripts(): array;

    public function getName(): string;

    public function getIcon(): string;

    public function getDescription(): string;
}
