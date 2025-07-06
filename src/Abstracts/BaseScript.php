<?php

namespace NodiLabs\NodiShell\Abstracts;

use NodiLabs\NodiShell\Contracts\ScriptInterface;

abstract class BaseScript implements ScriptInterface
{
    protected string $name = '';
    protected string $description = '';
    protected string $category = '';
    protected bool $productionSafe = false;
    protected array $parameters = [];
    protected array $tags = [];

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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Execute the script with provided parameters.
     * This method should be implemented by child classes.
     */
    abstract public function execute(array $parameters): mixed;

    /**
     * Helper method to get session from parameters.
     */
    protected function getSession(array $parameters): mixed
    {
        return $parameters['_session'] ?? null;
    }

    /**
     * Helper method to get variables from parameters.
     */
    protected function getVariables(array $parameters): array
    {
        return $parameters['_variables'] ?? [];
    }

    /**
     * Helper method to get a specific variable by name.
     */
    protected function getVariable(array $parameters, string $name, mixed $default = null): mixed
    {
        $variables = $this->getVariables($parameters);
        return $variables[$name] ?? $default;
    }

    /**
     * Helper method to create a standardized success response.
     */
    protected function success(mixed $data = null, string $message = 'Script executed successfully'): array
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    /**
     * Helper method to create a standardized error response.
     */
    protected function error(string $message, mixed $data = null): array
    {
        return [
            'success' => false,
            'error' => $message,
            'data' => $data,
        ];
    }

    /**
     * Helper method to validate required parameters.
     */
    protected function validateParameters(array $parameters, array $required = []): void
    {
        foreach ($required as $param) {
            if (!array_key_exists($param, $parameters) || empty($parameters[$param])) {
                throw new \InvalidArgumentException("Required parameter '{$param}' is missing or empty");
            }
        }
    }

    /**
     * Helper method to get parameter value with default.
     */
    protected function getParameter(array $parameters, string $name, mixed $default = null): mixed
    {
        return $parameters[$name] ?? $default;
    }
}
