<?php

namespace NodiLabs\NodiShell\Commands;

use Illuminate\Support\Str;

class MakeCheckCommand extends BaseNodiShellGeneratorCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'nodishell:check
        {name : The name of the system check}
        {--label= : Display label for the check}
        {--description= : Description of the check}
        {--force : Overwrite the check if it exists}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new NodiShell system check';

    /**
     * The type of class being generated.
     */
    protected $type = 'NodiShell System Check';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../Stubs/nodishell.check.stub';
    }

    /**
     * Get the namespace suffix for this generator type.
     */
    protected function getNamespaceSuffix(): string
    {
        return '\\Console\\NodiShell\\Checks';
    }

    /**
     * Get custom replacements for the stub.
     */
    protected function getCustomReplacements(string $name): array
    {
        $label = $this->option('label')
            ?: $this->ask('Check label', $this->toTitleCase(class_basename($name)));

        $description = $this->option('description')
            ?: $this->ask('Check description', "Verify {$label} is working properly");

        return [
            'label' => $label,
            'description' => $description,
            'contractNamespace' => 'NodiLabs\\NodiShell\\Contracts\\SystemCheckInterface',
            'dataNamespace' => 'NodiLabs\\NodiShell\\Data\\CheckResultData',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->qualifyClass($this->getNameInput());

        // Check if the check already exists
        if (! $this->option('force') && $this->alreadyExists($this->getNameInput())) {
            $this->components->error($this->type.' already exists!');

            return static::FAILURE;
        }

        // Make the directory if it doesn't exist
        $this->makeDirectory($this->getPath($name));

        // Generate the check file
        $this->files->put($this->getPath($name), $this->sortImports($this->buildClass($name)));

        $this->components->info(sprintf('%s [%s] created successfully.', $this->type, $name));

        // Provide helpful next steps
        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('1. Implement the run() method with your check logic');
        $this->line('2. Return an array of CheckResultData objects');
        $this->line('3. Run: php artisan nodishell to see system status');

        return static::SUCCESS;
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (! Str::endsWith($name, 'Check')) {
            $name .= 'Check';
        }

        return $name;
    }
}
