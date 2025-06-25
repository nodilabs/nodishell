<?php

namespace NodiLabs\NodiShell\Commands;

use Illuminate\Support\Str;

class MakeScriptCommand extends BaseNodiShellGeneratorCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'nodishell:script
        {name : The name of the script}
        {--category= : Category for the script}
        {--description= : Description of the script}
        {--production-safe : Mark the script as production safe}
        {--tags= : Comma-separated tags for the script}
        {--force : Overwrite the script if it exists}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new NodiShell script';

    /**
     * The type of class being generated.
     */
    protected $type = 'NodiShell Script';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../Stubs/nodishell.script.stub';
    }

    /**
     * Get the namespace suffix for this generator type.
     */
    protected function getNamespaceSuffix(): string
    {
        return '\\Console\\NodiShell\\Scripts';
    }

    /**
     * Get custom replacements for the stub.
     */
    protected function getCustomReplacements(string $name): array
    {
        $scriptName = $this->toTitleCase(class_basename($name));
        $description = $this->option('description')
            ?: $this->ask('Script description', "Execute {$scriptName}");

        $category = $this->option('category')
            ?: $this->ask('Script category', 'general');

        $productionSafe = $this->option('production-safe') ? 'true' : 'false';

        $tags = $this->option('tags')
            ? implode("', '", explode(',', $this->option('tags')))
            : $this->toKebabCase(class_basename($name));

        return [
            'scriptName' => $scriptName,
            'description' => $description,
            'category' => $category,
            'productionSafe' => $productionSafe,
            'tags' => $tags,
            'baseClassNamespace' => 'App\\Console\\NodiShell\\Scripts\\BaseScript',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->qualifyClass($this->getNameInput());

        // Check if the script already exists
        if (! $this->option('force') && $this->alreadyExists($this->getNameInput())) {
            $this->components->error($this->type.' already exists!');

            return static::FAILURE;
        }

        // Make the directory if it doesn't exist
        $this->makeDirectory($this->getPath($name));

        // Generate the script file
        $this->files->put($this->getPath($name), $this->sortImports($this->buildClass($name)));

        $this->components->info(sprintf('%s [%s] created successfully.', $this->type, $name));

        // Provide helpful next steps
        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('1. Implement the execute() method with your script logic');
        $this->line('2. Add parameters to the $parameters array if needed');
        $this->line('3. Add the script to a category\'s loadScripts() method');
        $this->line('4. Run: php artisan nodishell to test your script');

        return static::SUCCESS;
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (! Str::endsWith($name, 'Script')) {
            $name .= 'Script';
        }

        return $name;
    }
}
