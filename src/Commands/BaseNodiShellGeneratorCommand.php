<?php

namespace NodiLabs\NodiShell\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

abstract class BaseNodiShellGeneratorCommand extends GeneratorCommand
{
    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', '{{ namespace }}', '{{namespace}}'],
            [$this->getNamespace($name), $this->rootNamespace(), $this->getNamespace($name), $this->getNamespace($name)],
            $stub
        );

        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        $stub = $this->replaceCustomPlaceholders($stub, $name);

        return $stub;
    }

    /**
     * Replace custom placeholders in the stub.
     */
    protected function replaceCustomPlaceholders(string $stub, string $name): string
    {
        $replacements = $this->getCustomReplacements($name);

        foreach ($replacements as $placeholder => $value) {
            $stub = str_replace("{{ {$placeholder} }}", $value, $stub);
        }

        return $stub;
    }

    /**
     * Get custom replacements for the stub.
     * Override this method in child classes to provide specific replacements.
     */
    protected function getCustomReplacements(string $name): array
    {
        return [];
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.$this->getNamespaceSuffix();
    }

    /**
     * Get the namespace suffix for this generator type.
     */
    abstract protected function getNamespaceSuffix(): string;

    /**
     * Convert string to class name format.
     */
    protected function toClassName(string $name): string
    {
        return Str::studly($name);
    }

    /**
     * Convert string to kebab case.
     */
    protected function toKebabCase(string $name): string
    {
        return Str::kebab($name);
    }

    /**
     * Convert string to title case.
     */
    protected function toTitleCase(string $name): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $name));
    }
}
