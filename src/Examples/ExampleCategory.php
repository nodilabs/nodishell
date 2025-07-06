<?php

namespace NodiLabs\NodiShell\Examples;

use NodiLabs\NodiShell\Abstracts\BaseCategory;

/**
 * Example category demonstrating BaseCategory usage.
 * This file shows how to extend BaseCategory with common functionality.
 */
final class ExampleCategory extends BaseCategory
{
    protected string $name = 'Examples';
    protected string $description = 'Example scripts demonstrating NodiShell functionality';
    protected string $icon = '📚';
    protected string $color = 'green';
    protected int $sortOrder = 10;
    protected bool $enabled = true;

    protected function loadScripts(): void
    {
        // Add scripts using helper methods
        $this->addScript(new ExampleScript());

        // You can also add multiple scripts at once
        // $this->addScripts([
        //     new ExampleScript(),
        //     new AnotherExampleScript(),
        // ]);
    }

    /**
     * Custom method to demonstrate extended functionality.
     */
    public function getExampleCount(): int
    {
        return count($this->getScriptsByTag('example'));
    }

    /**
     * Custom method to get tutorial scripts.
     */
    public function getTutorialScripts(): array
    {
        return $this->getScriptsByTag('tutorial');
    }
}
