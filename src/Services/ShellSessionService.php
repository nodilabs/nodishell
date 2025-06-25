<?php

namespace NodiLabs\NodiShell\Services;

final class ShellSessionService
{
    private array $history = [];

    private array $variables = [];

    private array $context = [];

    private int $maxHistorySize = 100;

    public function addToHistory(string $command): void
    {
        $this->history[] = $command;

        if (count($this->history) > $this->maxHistorySize) {
            array_shift($this->history);
        }
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function clearHistory(): void
    {
        $this->history = [];
    }

    public function setVariable(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }

    public function getVariable(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    public function hasVariable(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    public function removeVariable(string $name): void
    {
        unset($this->variables[$name]);
    }

    public function getAllVariables(): array
    {
        return $this->variables;
    }

    public function clearVariables(): void
    {
        $this->variables = [];
    }

    public function setContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    public function getContext(string $key): mixed
    {
        return $this->context[$key] ?? null;
    }

    public function getAllContext(): array
    {
        return $this->context;
    }

    public function clearContext(): void
    {
        $this->context = [];
    }

    public function reset(): void
    {
        $this->clearHistory();
        $this->clearVariables();
        $this->clearContext();
    }
}
