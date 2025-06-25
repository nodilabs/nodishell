<?php

namespace NodiLabs\NodiShell\Commands;

use Illuminate\Support\Str;

class MakeCategoryCommand extends BaseNodiShellGeneratorCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'nodishell:category
        {name : The name of the category}
        {--description= : Description of the category}
        {--icon= : Emoji icon for the category}
        {--color=blue : Color theme for the category}
        {--sort-order=100 : Sort order for the category}
        {--force : Overwrite the category if it exists}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new NodiShell category';

    /**
     * The type of class being generated.
     */
    protected $type = 'NodiShell Category';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../Stubs/nodishell.category.stub';
    }

    /**
     * Get the namespace suffix for this generator type.
     */
    protected function getNamespaceSuffix(): string
    {
        return '\\Console\\NodiShell\\Categories';
    }

    /**
     * Get custom replacements for the stub.
     */
    protected function getCustomReplacements(string $name): array
    {
        $categoryName = $this->option('description')
            ?: $this->ask('Category display name', $this->toTitleCase(class_basename($name)));

        $description = $this->option('description')
            ?: $this->ask('Category description', "Scripts for {$categoryName}");

        $icon = $this->option('icon')
            ?: $this->ask('Category icon (emoji)', '📁');

        $color = $this->option('color') ?: 'blue';
        $sortOrder = $this->option('sort-order') ?: 100;

        return [
            'categoryName' => $categoryName,
            'description' => $description,
            'icon' => $icon,
            'color' => $color,
            'sortOrder' => $sortOrder,
            'baseClassNamespace' => 'App\\Console\\NodiShell\\Categories\\BaseCategory',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        $name = $this->qualifyClass($this->getNameInput());

        // Check if the category already exists
        if (! $this->option('force') && $this->alreadyExists($this->getNameInput())) {
            $this->components->error($this->type.' already exists!');

            return static::FAILURE;
        }

        // Make the directory if it doesn't exist
        $this->makeDirectory($this->getPath($name));

        // Generate the category file
        $this->files->put($this->getPath($name), $this->sortImports($this->buildClass($name)));

        $this->components->info(sprintf('%s [%s] created successfully.', $this->type, $name));

        // Provide helpful next steps
        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('1. Add scripts to the loadScripts() method');
        $this->line('2. Run: php artisan nodishell to see your new category');

        return static::SUCCESS;
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (! Str::endsWith($name, 'Category')) {
            $name .= 'Category';
        }

        return $name;
    }
}
