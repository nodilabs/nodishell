<?php

namespace NodiLabs\NodiShell\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use NodiLabs\NodiShell\Contracts\ScriptInterface;
use NodiLabs\NodiShell\Services\CategoryDiscoveryService;
use NodiLabs\NodiShell\Services\ScriptDiscoveryService;
use NodiLabs\NodiShell\Services\ShellSessionService;
use NodiLabs\NodiShell\Services\SystemCheckService;
use Symfony\Component\Console\Helper\Table;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

final class NodiShellCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nodishell
                           {--mode=interactive : Shell mode (interactive|script)}
                           {--category= : Start in specific category}
                           {--script= : Execute specific script}
                           {--safe-mode : Enable production safety checks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advanced Laravel interactive shell with autocomplete and script repository';

    private bool $running = true;

    private string $currentCategory = 'main';

    private ShellSessionService $session;

    public function __construct(
        private readonly ScriptDiscoveryService $scriptDiscoveryService,
        private readonly CategoryDiscoveryService $CategoryDiscoveryService,
        private readonly SystemCheckService $systemCheckService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        $this->session = new ShellSessionService;

        $this->displayWelcome();

        // Handle direct script execution
        if ($this->option('script')) {
            $this->executeScript($this->option('script'));
            return true;
        }

        // Handle category start
        if ($this->option('category')) {
            $this->currentCategory = $this->option('category');
        }

        // Start main shell loop
        $this->startShellLoop();

        return true;
    }

    private function displayWelcome(): void
    {
        $this->line('');
        $this->displayWelcomeHeader();
        $this->line('');

        $safeMode = config('nodishell.production_safety.safe_mode', true);

        if (app()->environment('production') && ! $this->option('safe-mode') && $safeMode) {
            warning('⚠️  Running in PRODUCTION environment. Use --safe-mode for additional safety checks.');
            $this->line('');
        }
    }

    private function displayWelcomeHeader(): void
    {
        $title = config('nodishell.branding.title', '🚀 NodiShell');
        $subtitle = config('nodishell.branding.subtitle', 'Advanced Laravel Interactive Shell');

        // Fixed width for consistency
        $totalWidth = 60;
        $contentWidth = $totalWidth - 4; // Account for borders and padding
        $border = str_repeat('─', $totalWidth - 2);

        // Calculate actual display width (emojis count as 2 chars in display)
        $titleDisplayWidth = $this->getDisplayWidth($title);
        $subtitleDisplayWidth = $this->getDisplayWidth($subtitle);

        $titlePadding = $contentWidth - $titleDisplayWidth;
        $subtitlePadding = $contentWidth - $subtitleDisplayWidth;

        $this->line('<fg=cyan>╭'.$border.'╮</>');
        $this->line('<fg=cyan>│</> <fg=yellow>'.$title.str_repeat(' ', max(0, $titlePadding)).'</> <fg=cyan>│</>');
        $this->line('<fg=cyan>│</> <fg=white>'.$subtitle.str_repeat(' ', max(0, $subtitlePadding)).'</> <fg=cyan>│</>');
        $this->line('<fg=cyan>╰'.$border.'╯</>');
    }

    private function startShellLoop(): void
    {
        while ($this->running) {
            try {
                if ($this->currentCategory === 'main') {
                    $this->showMainMenu();
                } else {
                    $this->showCategoryMenu($this->currentCategory);
                }
            } catch (\Exception $e) {
                error("Error: {$e->getMessage()} - {$e->getFile()} - {$e->getLine()}");
                $this->currentCategory = 'main';
            }
        }

        info('👋 Goodbye! Thanks for using NodiShell.');
    }

    private function showMainMenu(): void
    {
        $categories = $this->CategoryDiscoveryService->getAllCategories();
        $categoryOptions = [];

        foreach ($categories as $key => $category) {
            $scriptCount = count($category->getScripts());
            $categoryOptions[$key] = "{$category->getIcon()} {$category->getName()} ({$scriptCount} scripts)";
        }

        $quickActions = [];

        if (config('nodishell.features.search', true)) {
            $quickActions['search'] = '🔍 Search Scripts';
        }
        if (config('nodishell.features.raw_php', true)) {
            $quickActions['raw-php'] = '⚡ Execute Raw PHP';
        }
        if (config('nodishell.features.variable_manager', true)) {
            $quickActions['variables'] = '💾 Manage Variables';
        }
        if (config('nodishell.features.system_status', true)) {
            $quickActions['system-status'] = '📊 System Status';
        }

        $quickActions['history'] = '📜 Command History';
        $quickActions['exit'] = '❌ Exit NodiShell';

        $allOptions = [
            'Categories' => $categoryOptions,
            'Quick Actions' => $quickActions,
        ];

        $flatOptions = [];

        foreach ($allOptions as $group => $options) {
            foreach ($options as $key => $label) {
                $flatOptions[$key] = "[$group] $label";
            }
        }

        $choice = select(
            label: 'What would you like to do?',
            options: $flatOptions,
            hint: 'Use arrow keys to navigate, Enter to select'
        );

        $this->handleMainMenuChoice($choice);
    }

    private function handleMainMenuChoice(string $choice): void
    {
        $features = config('nodishell.features', []);

        switch ($choice) {
            case 'search':
                if ($features['search'] ?? true) {
                    $this->showSearchInterface();
                }
                break;
            case 'raw-php':
                if ($features['raw_php'] ?? true) {
                    $this->executeRawPhp();
                }
                break;
            case 'variables':
                if ($features['variable_manager'] ?? true) {
                    $this->showVariableManager();
                }
                break;
            case 'system-status':
                if ($features['system_status'] ?? true) {
                    $this->showSystemStatus();
                }
                break;
            case 'history':
                $this->showCommandHistory();
                break;
            case 'exit':
                $this->running = false;
                break;
            default:
                $this->currentCategory = $choice;
        }
    }

    private function showCategoryMenu(string $categoryKey): void
    {
        $category = $this->CategoryDiscoveryService->getCategory($categoryKey);

        if (! $category) {
            error("Category '{$categoryKey}' not found.");
            $this->currentCategory = 'main';

            return;
        }

        $this->line('');
        $this->displayCategoryHeader($category);
        $this->line('');

        $scripts = $category->getScripts();
        $scriptOptions = [];

        foreach ($scripts as $script) {
            $safetyIcon = $script->isProductionSafe() ? '✅' : '⚠️';
            $scriptOptions[$script->getName()] = "{$safetyIcon} {$script->getName()} - {$script->getDescription()}";
        }

        $scriptOptions['search-category'] = '🔍 Search in this category';
        $scriptOptions['back'] = '← Back to main menu';

        $choice = select(
            label: 'Select a script to execute:',
            options: $scriptOptions,
            hint: 'Scripts marked with ⚠️ require extra caution in production'
        );

        $this->handleCategoryChoice($choice, $category);
    }

    private function handleCategoryChoice(string $choice, $category): void
    {
        match ($choice) {
            'search-category' => $this->searchInCategory($category),
            'back' => $this->currentCategory = 'main',
            default => $this->executeScriptByName($choice, $category)
        };
    }

    private function showSearchInterface(): void
    {
        $result = search(
            label: 'Search for scripts and operations',
            placeholder: 'E.g. "user cleanup", "cache clear", "test data"',
            options: fn (string $value) => $this->scriptDiscoveryService
                ->search($value)
                ->take(15)
                ->mapWithKeys(fn (ScriptInterface $script) => [
                    $script->getName() => "[{$script->getCategory()}] → {$script->getName()} - {$script->getDescription()}",
                ])
                ->toArray()
        );

        if ($result) {
            $this->executeScriptById($result);
        }
    }

    private function executeRawPhp(): void
    {
        $this->line('');
        $this->line('<fg=yellow>⚡ Raw PHP Execution Mode</>');
        $this->line('');

        $options = [
            'tinker' => '🔧 Launch Laravel Tinker (Full REPL)',
            'inline' => '⚡ Inline PHP Execution (Simple eval)',
            'back' => '← Back to main menu',
        ];

        $choice = select(
            label: 'Choose PHP execution mode:',
            options: $options,
            hint: 'Tinker provides a full REPL with autocomplete, history, and Laravel features'
        );

        match ($choice) {
            'tinker' => $this->launchTinker(),
            'inline' => $this->executeInlinePhp(),
            'back' => null,
            default => null
        };
    }

    private function launchTinker(): void
    {
        $this->line('');

        $variables = $this->session->getAllVariables();

        if (! empty($variables)) {
            info('🚀 Launching Laravel Tinker with NodiShell variables...');
        } else {
            info('🚀 Launching Laravel Tinker...');
            $this->line('<fg=gray>No NodiShell variables to inject</>');
        }

        $this->line('<fg=gray>Type "exit" to return to NodiShell or press Ctrl+C to exit</>');
        $this->line('');

        // Small delay to let user read the message
        usleep(1000000); // 1 second

        // Create a custom Tinker command with injected variables
        $this->launchTinkerWithVariables($variables);

        $this->line('');
        info('👋 Welcome back to NodiShell!');
    }

    private function launchTinkerWithVariables(array $variables): void
    {
        if (empty($variables)) {
            // No variables to inject, just launch normal Tinker
            $this->call('tinker');

            return;
        }

        // Create a temporary file with variable assignments
        $tempFile = tempnam(sys_get_temp_dir(), 'nodishell_vars_').'.php';

        $content = "<?php\n";
        $content .= '// NodiShell Variables - Auto-loaded at '.now()->toDateTimeString()."\n\n";

        // Add a welcome message
        $content .= "echo \"\\n\\033[32m✅ NodiShell variables loaded successfully!\\033[0m\\n\";\n";
        $content .= "echo \"\\033[36mAvailable variables:\\033[0m\\n\";\n";

        foreach ($variables as $name => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);

            // Export the variable
            if (is_string($value)) {
                $content .= "\${$name} = ".var_export($value, true).";\n";
            } elseif (is_numeric($value) || is_bool($value) || is_null($value)) {
                $content .= "\${$name} = ".var_export($value, true).";\n";
            } elseif (is_array($value)) {
                $content .= "\${$name} = ".var_export($value, true).";\n";
            } elseif (is_object($value)) {
                // For objects, we'll serialize them and unserialize in Tinker
                $serialized = base64_encode(serialize($value));
                $content .= "\${$name} = unserialize(base64_decode('{$serialized}'));\n";
            } else {
                $content .= "\${$name} = ".var_export($value, true).";\n";
            }

            // Show variable info
            $content .= "echo \"  \\033[34m\\\${$name}\\033[0m \\033[90m({$type})\\033[0m\\n\";\n";
        }

        $content .= "\n";
        $content .= "// Helper function to list all NodiShell variables\n";
        $content .= "function nodishell_vars() {\n";
        $content .= "    echo \"\\n\\033[36mnodishell Variables:\\033[0m\\n\";\n";

        foreach ($variables as $name => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            $content .= "    echo \"  \\033[34m\\\${$name}\\033[0m \\033[90m({$type})\\033[0m\\n\";\n";
        }

        $content .= "    echo \"\\n\";\n";
        $content .= "}\n\n";

        $content .= "echo \"\\033[33mTip: Use \\033[36mnodishell_vars()\\033[33m to list all variables\\033[0m\\n\\n\";\n";

        file_put_contents($tempFile, $content);

        // Show what we're loading
        $this->line('');
        $this->line('<fg=yellow>🚀 Launching Tinker with NodiShell variables...</>');

        try {
            // Launch Tinker with the include file
            $this->call('tinker', ['include' => [$tempFile]]);
        } finally {
            // Clean up the temporary file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        $this->session->addToHistory('tinker');

        $this->line('');
        $this->pressEnterToContinue('Press Enter to return to menu...');

    }

    private function executeInlinePhp(): void
    {
        $this->line('');
        $this->line('<fg=yellow>⚡ Inline PHP Execution Mode</>');
        $this->line('<fg=gray>Enter PHP code to execute (without opening <?php tag)</>');
        $this->line('<fg=gray>Access session variables with: $this->session->getVariable("name")</>');
        $this->line('<fg=gray>Type "exit" to return to main menu</>');
        $this->line('');

        // Make session available as $session
        $session = $this->session;

        // Extract variables into local scope
        $variables = $this->session->getAllVariables();

        foreach ($variables as $name => $value) {
            $$name = $value;
        }

        while (true) {
            $code = $this->ask('PHP> ');

            if (strtolower($code) === 'exit') {
                break;
            }

            if (empty($code)) {
                continue;
            }

            try {
                // Execute in closure to have access to variables
                $closure = function () use ($code, $variables) {
                    // Extract variables into closure scope
                    foreach ($variables as $name => $value) {
                        $$name = $value;
                    }

                    return eval("return {$code};");
                };

                $result = $closure();

                if ($result !== null) {
                    $this->displayResult($result);

                    // Ask if user wants to store the result
                    if (confirm('Store result in a variable?', false)) {
                        $varName = $this->ask('Variable name:');

                        if (! empty($varName)) {
                            $this->session->setVariable($varName, $result);
                            info("✅ Result stored in \${$varName}");
                        }
                    }
                } else {
                    $this->line('<fg=green>✓ Executed successfully</>');
                }

                // Refresh variables for next iteration
                $variables = $this->session->getAllVariables();
            } catch (\Exception $e) {
                error("Error: {$e->getMessage()}");
            } catch (\ParseError $e) {
                error("Parse Error: {$e->getMessage()}");
            }
        }
    }

    private function showSystemStatus(): void
    {
        $this->line('');
        $this->line('<fg=yellow>📊 System Status</>');
        $this->line('');

        $statusData = [
            ['<fg=cyan>Environment</>', app()->environment()],
            ['<fg=cyan>Laravel Version</>', app()->version()],
            ['<fg=cyan>PHP Version</>', phpversion()],
            ['<fg=cyan>Memory Usage</>', $this->formatBytes(memory_get_usage(true))],
            ['<fg=cyan>Memory Peak</>', $this->formatBytes(memory_get_peak_usage(true))],
            ['<fg=cyan>Database Connection</>', $this->getDatabaseStatus()],
            ['<fg=cyan>Cache Driver</>', config('cache.default')],
            ['<fg=cyan>Queue Driver</>', config('queue.default')],
        ];

        table(['Property', 'Value'], $statusData);

        $this->line('');
        $this->info('Running system checks...');

        $checks = $this->systemCheckService->getChecks();
        if ($checks->isEmpty()) {
            $this->line('<fg=gray>No custom system checks configured.</>');
        } else {
            $results = $checks->flatMap(function (\NodiLabs\NodiShell\Contracts\SystemCheckInterface $check) {
                $checkResults = $check->run();

                // Each result from the check becomes a row in the table
                return collect($checkResults)->map(function (\NodiLabs\NodiShell\Data\CheckResultData $result) use ($check) {
                    return [
                        $check->getLabel(),
                        $result->successful ? '<info>✔ Pass</info>' : '<e>✘ Fail</e>',
                        $result->message,
                    ];
                });
            });

            if ($results->isNotEmpty()) {
                table(['Check', 'Status', 'Message'], $results->all());
            } else {
                $this->line('<fg=gray>All checks ran, but returned no results.</>');
            }
        }

        $this->pressEnterToContinue();
    }

    private function showCommandHistory(): void
    {
        $history = $this->session->getHistory();

        if (empty($history)) {
            info('No command history available.');

            return;
        }

        $this->line('<fg=yellow>📜 Command History</>');

        foreach ($history as $index => $command) {
            $this->line(sprintf('  %d. %s', $index + 1, $command));
        }

        $this->pressEnterToContinue();
    }

    private function searchInCategory($category): void
    {
        $scripts = $category->getScripts();

        $result = search(
            label: "Search scripts in {$category->getName()}",
            placeholder: 'Type to filter scripts...',
            options: fn (string $value) => collect($scripts)
                ->filter(fn ($script) => str_contains(strtolower($script->getName()), strtolower($value)) ||
                    str_contains(strtolower($script->getDescription()), strtolower($value))
                )
                ->mapWithKeys(fn ($script) => [
                    $script->getName() => "{$script->getName()} - {$script->getDescription()}",
                ])
                ->toArray()
        );

        if ($result) {
            $this->executeScriptByName($result, $category);
        }
    }

    private function executeScriptByName(string $scriptName, $category): void
    {
        $script = collect($category->getScripts())
            ->first(fn ($script) => $script->getName() === $scriptName);

        if (! $script) {
            error("Script '{$scriptName}' not found.");

            return;
        }

        $this->executeScriptInstance($script);
    }

    private function executeScriptById(string $scriptId): void
    {
        $script = $this->scriptDiscoveryService->findById($scriptId);

        if (! $script) {
            error("Script with ID '{$scriptId}' not found.");

            return;
        }

        $this->executeScriptInstance($script);
    }

    private function executeScript(string $scriptName): int
    {
        $script = $this->scriptDiscoveryService->findById($scriptName);

        if (! $script) {
            $this->error("Script '{$scriptName}' not found.");

            return 1;
        }

        return $this->executeScriptInstance($script);
    }

    private function executeScriptInstance($script): int
    {
        try {
            $safeMode = config('nodishell.production_safety.safe_mode', true);
            // Production safety check
            if (app()->environment('production') && ! $script->isProductionSafe()) {
                if (! $this->option('safe-mode') && $safeMode) {
                    warning('⚠️  This script is not marked as production-safe.');

                    $confirmed = confirm(
                        label: 'Do you want to continue anyway?',
                        default: false,
                        yes: 'Yes, I understand the risks',
                        no: 'Cancel operation'
                    );

                    if (! $confirmed) {
                        info('Operation cancelled.');
                        $this->pressEnterToContinue('Press Enter to continue...');

                        return 0;
                    }
                }
            }

            // Get script parameters if needed
            $parameters = [];
            $requiredParams = $script->getParameters();

            if (! empty($requiredParams)) {
                $this->line('📝 This script requires parameters:');

                foreach ($requiredParams as $param) {
                    $value = $this->ask($param['label'] ?? $param['name']);
                    $parameters[$param['name']] = $value;
                }
            }

            // Execute the script
            $this->line('');
            info("🚀 Executing: {$script->getName()}");

            // Make session variables available to the script
            $enhancedParameters = array_merge($parameters, [
                '_session' => $this->session,
                '_variables' => $this->session->getAllVariables(),
            ]);

            $result = $script->execute($enhancedParameters);

            $this->line('');
            info('✅ Script executed successfully!');

            if ($result !== null) {
                $this->displayResult($result);

                // Auto-store result in a variable
                $this->storeScriptResult($script->getName(), $result);
            }

            // Add to history
            $this->session->addToHistory($script->getName());

            // Pause to let user review results
            $this->line('');
            $this->pressEnterToContinue('Press Enter to return to menu...');

            return 0;
        } catch (\Exception $e) {
            error("Script execution failed: {$e->getMessage()}");

            // Pause on error as well
            $this->line('');
            $this->pressEnterToContinue('Press Enter to return to menu...');

            return 1;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    private function getDatabaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return '✅ Connected';
        } catch (\Exception $e) {
            return '❌ Failed';
        }
    }

    private function displayResult($result): void
    {
        $this->line('');
        $this->line('<fg=yellow>🚀 Script Result:</>');

        // Format the content first
        $content = $this->formatForTinker($result);
        $contentLines = explode("\n", $content);

        // Calculate box dimensions
        $maxWidth = 0;

        foreach ($contentLines as $line) {
            $lineWidth = $this->getDisplayWidth($line);

            if ($lineWidth > $maxWidth) {
                $maxWidth = $lineWidth;
            }
        }
        $totalWidth = $maxWidth + 4; // Add padding
        $border = str_repeat('─', $totalWidth - 2);

        // Draw the box
        $this->line('<fg=cyan>╭'.$border.'╮</>');

        foreach ($contentLines as $line) {
            $padding = $totalWidth - $this->getDisplayWidth($line) - 3; // 2 for border, 1 for space
            $this->line('<fg=cyan>│</> '.$line.str_repeat(' ', max(0, $padding)).'<fg=cyan>│</>');
        }

        $this->line('<fg=cyan>╰'.$border.'╯</>');
        $this->line('');
    }

    private function formatForTinker($value, int $depth = 0): string
    {
        $indent = str_repeat('  ', $depth);

        if (is_null($value)) {
            return '<fg=yellow>null</>';
        }

        if (is_bool($value)) {
            return $value ? '<fg=yellow>true</>' : '<fg=yellow>false</>';
        }

        if (is_string($value)) {
            return '<fg=green>"'.addslashes($value).'"</>';
        }

        if (is_numeric($value)) {
            return '<fg=magenta>'.$value.'</>';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return '<fg=white>[]</>';
            }

            $items = [];

            foreach ($value as $key => $item) {
                $formattedKey = is_string($key) ? '<fg=blue>"'.$key.'"</>' : '<fg=magenta>'.$key.'</>';
                $formattedValue = $this->formatForTinker($item, $depth + 1);
                $items[] = $indent.'  '.$formattedKey.' => '.$formattedValue;
            }

            return "<fg=white>[</>\n".implode(",\n", $items)."\n{$indent}<fg=white>]</>";
        }

        if (is_object($value)) {
            return $this->formatObjectForTinker($value, $depth);
        }

        return '<fg=cyan>'.var_export($value, true).'</>';
    }

    private function formatObjectForTinker($object, int $depth = 0): string
    {
        $indent = str_repeat('  ', $depth);
        $className = get_class($object);

        // Get object properties
        $reflection = new \ReflectionClass($object);
        $properties = [];

        // Get public properties
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (! $property->isStatic()) {
                $name = $property->getName();
                $value = $property->getValue($object);
                $properties[$name] = $value;
            }
        }

        // For Eloquent models, try to get attributes
        if (method_exists($object, 'getAttributes')) {
            $attributes = $object->getAttributes();
            $properties = array_merge($properties, $attributes);
        }

        // Try to get array representation
        if (method_exists($object, 'toArray') && empty($properties)) {
            $properties = $object->toArray();
        }

        // Build the output - Start with class name and object hash
        $objectHash = '#'.spl_object_id($object);
        $output = "<fg=cyan>{$className}</> <fg=white>{$objectHash}</>";

        if (! empty($properties)) {
            $propertyLines = [];

            foreach ($properties as $key => $value) {
                // Handle protected/private properties prefix
                $displayKey = $key;

                if (str_starts_with($key, "\0*\0")) {
                    $displayKey = '#'.substr($key, 3); // Protected property
                } elseif (str_contains($key, "\0")) {
                    $displayKey = '-'.substr($key, strrpos($key, "\0") + 1); // Private property
                }

                $formattedValue = $this->formatForTinker($value, $depth + 1);
                $propertyLines[] = $indent.'  '.$displayKey.': '.$formattedValue;
            }

            $output .= "\n".implode(",\n", $propertyLines)."\n{$indent}<fg=white>}</>";
        } else {
            $output .= "\n{$indent}<fg=white>}</>";
        }

        return $output;
    }

    private function displayCategoryHeader($category): void
    {
        $titleContent = "{$category->getIcon()} {$category->getName()}";
        $descContent = $category->getDescription();

        // Calculate minimum width needed using actual display width
        $titleDisplayWidth = $this->getDisplayWidth($titleContent);
        $descDisplayWidth = $this->getDisplayWidth($descContent);
        $minWidth = max($titleDisplayWidth, $descDisplayWidth) + 6; // +6 for padding and safety

        // Use a reasonable minimum width
        $totalWidth = max($minWidth, 70);
        $contentWidth = $totalWidth - 4; // Account for borders and spaces
        $border = str_repeat('─', $totalWidth - 2);

        $titlePadding = $contentWidth - $titleDisplayWidth;
        $descPadding = $contentWidth - $descDisplayWidth;

        $this->line('<fg=cyan>╭'.$border.'╮</>');
        $this->line('<fg=cyan>│</> <fg=yellow>'.$titleContent.str_repeat(' ', max(0, $titlePadding)).'</> <fg=cyan>│</>');
        $this->line('<fg=cyan>├'.$border.'┤</>');
        $this->line('<fg=cyan>│</> <fg=gray>'.$descContent.str_repeat(' ', max(0, $descPadding)).'</> <fg=cyan>│</>');
        $this->line('<fg=cyan>╰'.$border.'╯</>');
    }

    private function getDisplayWidth(string $text): int
    {
        // Remove color codes first
        $cleanText = preg_replace('/<[^>]*>/', '', $text);

        // Count emojis and wide characters
        $emojiCount = preg_match_all('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $cleanText);

        // Base length + extra space for emojis (they take 2 display chars)
        return mb_strlen($cleanText) + $emojiCount;
    }

    private function storeScriptResult(string $scriptName, mixed $result): void
    {
        // Create a clean variable name from script name
        $variableName = 'result_'.preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($scriptName));

        $this->session->setVariable($variableName, $result);

        $this->line('');
        $this->line("<fg=blue>💾 Result stored in variable: \${$variableName}</>");
    }

    private function showVariableManager(): void
    {
        $variables = $this->session->getAllVariables();

        $this->line('');
        $this->line('<fg=yellow>💾 Variable Manager</>');
        $this->line('');

        if (empty($variables)) {
            info('No variables stored in this session.');
            note('Variables are automatically created when scripts return results.');
            $this->pressEnterToContinue();

            return;
        }

        $options = [
            'list' => '📋 List all variables',
            'view' => '👁️ View variable content',
            'set' => '✏️ Set new variable',
            'delete' => '🗑️ Delete variable',
            'clear' => '🧹 Clear all variables',
            'back' => '← Back to main menu',
        ];

        $choice = select(
            label: 'Variable management options:',
            options: $options,
            hint: 'Variables persist throughout your NodiShell session'
        );

        match ($choice) {
            'list' => $this->listVariables(),
            'view' => $this->viewVariable(),
            'set' => $this->setVariable(),
            'delete' => $this->deleteVariable(),
            'clear' => $this->clearVariables(),
            'back' => null,
            default => null
        };
    }

    private function listVariables(): void
    {
        $variables = $this->session->getAllVariables();

        $this->line('');
        $this->line('<fg=yellow>📋 Session Variables:</>');
        $this->line('');

        $headers = ['Variable', 'Type', 'Preview'];
        $rows = [];

        foreach ($variables as $name => $value) {
            $type = gettype($value);

            if (is_object($value)) {
                $type = get_class($value);
            }

            $preview = $this->getValuePreview($value);
            $rows[] = ["\${$name}", $type, $preview];
        }

        table($headers, $rows);

        $this->pressEnterToContinue();
    }

    private function viewVariable(): void
    {
        $variables = $this->session->getAllVariables();

        if (empty($variables)) {
            info('No variables available.');

            return;
        }

        $variableChoice = search(
            label: 'Select variable to view:',
            placeholder: 'Type variable name...',
            options: fn (string $value) => collect($variables)
                ->keys()
                ->filter(fn ($name) => str_contains(strtolower($name), strtolower($value)))
                ->mapWithKeys(fn ($name) => [$name => "\${$name} (".gettype($variables[$name]).')'])
                ->toArray()
        );

        if ($variableChoice) {
            $value = $this->session->getVariable($variableChoice);
            $this->line('');
            $this->line("<fg=blue>Variable: \${$variableChoice}</>");
            $this->displayResult($value);

            $this->pressEnterToContinue();
        }
    }

    private function setVariable(): void
    {
        $name = $this->ask('Variable name (without $):');

        if (empty($name)) {
            warning('Variable name cannot be empty.');

            return;
        }

        $valueInput = $this->ask('Variable value (JSON or simple value):');

        try {
            // Try to decode as JSON first
            $value = json_decode($valueInput, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // If not JSON, store as string
            $value = $valueInput;
        }

        $this->session->setVariable($name, $value);
        info("✅ Variable \${$name} set successfully!");

        $this->pressEnterToContinue();
    }

    private function deleteVariable(): void
    {
        $variables = $this->session->getAllVariables();

        if (empty($variables)) {
            info('No variables available to delete.');

            return;
        }

        $variableChoice = search(
            label: 'Select variable to delete:',
            placeholder: 'Type variable name...',
            options: fn (string $value) => collect($variables)
                ->keys()
                ->filter(fn ($name) => str_contains(strtolower($name), strtolower($value)))
                ->mapWithKeys(fn ($name) => [$name => "\${$name}"])
                ->toArray()
        );

        if ($variableChoice) {
            $confirmed = confirm("Are you sure you want to delete \${$variableChoice}?");

            if ($confirmed) {
                $this->session->removeVariable($variableChoice);
                info("✅ Variable \${$variableChoice} deleted!");
            }
        }

        $this->pressEnterToContinue();
    }

    private function clearVariables(): void
    {
        $variables = $this->session->getAllVariables();

        if (empty($variables)) {
            info('No variables to clear.');

            return;
        }

        $confirmed = confirm(
            label: 'Are you sure you want to clear ALL variables?',
            default: false,
            yes: 'Yes, delete all',
            no: 'Cancel'
        );

        if ($confirmed) {
            $this->session->clearVariables();
            info('✅ All variables cleared!');
        }

        $this->pressEnterToContinue();
    }

    private function getValuePreview(mixed $value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return '"'.(strlen($value) > 30 ? substr($value, 0, 30).'...' : $value).'"';
        }

        if (is_array($value)) {
            return '['.count($value).' items]';
        }

        if (is_object($value)) {
            return get_class($value).' object';
        }

        return 'unknown';
    }

    private function pressEnterToContinue(string $message = 'Press enter to continue...'): void
    {
        pause($message);
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'mode' => 'What mode should the shell run in?',
            'category' => 'Which category should we start in?',
        ];
    }
}
