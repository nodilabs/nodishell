<?php

namespace NodiLabs\NodiShell\Tests\Integration;

use NodiLabs\NodiShell\Services\CategoryDiscoveryService;
use NodiLabs\NodiShell\Services\ScriptDiscoveryService;
use NodiLabs\NodiShell\Services\ShellSessionService;
use NodiLabs\NodiShell\Tests\Fixtures\TestCategory;
use NodiLabs\NodiShell\Tests\Fixtures\TestScript;
use NodiLabs\NodiShell\Contracts\ScriptInterface;

describe('Service Integration', function () {
    beforeEach(function () {
        $this->categoryService = new CategoryDiscoveryService();
        $this->scriptService = new ScriptDiscoveryService($this->categoryService);
        $this->sessionService = new ShellSessionService();
    });

    it('can discover and search scripts across categories', function () {
        // Create test scripts
        $userScript = new TestScript('user-create', 'Create user', ['user'], 'users');
        $adminScript = new TestScript('admin-panel', 'Admin panel access', ['admin'], 'admin');
        $cacheScript = new TestScript('cache-clear', 'Clear cache', ['cache'], 'maintenance');

        // Create categories with scripts
        $userCategory = new TestCategory('Users', 'User management', '👥', 1, true, [$userScript]);
        $adminCategory = new TestCategory('Admin', 'Admin tools', '⚙️', 2, true, [$adminScript]);
        $maintenanceCategory = new TestCategory('Maintenance', 'System maintenance', '🔧', 3, true, [$cacheScript]);

        // Register categories
        $this->categoryService->registerCategory('users', $userCategory);
        $this->categoryService->registerCategory('admin', $adminCategory);
        $this->categoryService->registerCategory('maintenance', $maintenanceCategory);

        // Test cross-category script search
        $userResults = $this->scriptService->search('user');
        expect($userResults)->toHaveCount(1);
        expect($userResults->first()->getName())->toBe('user-create');

        $adminResults = $this->scriptService->search('admin');
        expect($adminResults)->toHaveCount(1);
        expect($adminResults->first()->getName())->toBe('admin-panel');

        // Test finding scripts by exact ID
        $foundScript = $this->scriptService->findById('cache-clear');
        expect($foundScript)->not()->toBeNull();
        expect($foundScript->getName())->toBe('cache-clear');
    });

    it('can execute scripts with session context', function () {
        // Create a script that uses session data
        $script = new class implements ScriptInterface {
            public function getName(): string { return 'session-test'; }
            public function getDescription(): string { return 'Session test script'; }
            public function getTags(): array { return ['test']; }
            public function getCategory(): string { return 'test'; }
            public function isProductionSafe(): bool { return true; }
            public function getPreview(): ?string { return null; }
            public function getParameters(): array { return []; }

            public function execute(array $parameters): mixed
            {
                $session = $parameters['_session'] ?? null;
                $variables = $parameters['_variables'] ?? [];

                return [
                    'session_provided' => $session !== null,
                    'variables_count' => count($variables),
                    'parameters' => $parameters
                ];
            }
        };

        // Set up session data
        $this->sessionService->setVariable('user_id', 123);
        $this->sessionService->setVariable('environment', 'test');

        // Execute script with session context
        $result = $script->execute([
            '_session' => $this->sessionService,
            '_variables' => $this->sessionService->getAllVariables(),
            'custom_param' => 'test_value'
        ]);

        expect($result['session_provided'])->toBeTrue();
        expect($result['variables_count'])->toBe(2);
        expect($result['parameters']['custom_param'])->toBe('test_value');
    });

    it('can filter categories and maintain script relationships', function () {
        // Create scripts and categories
        $enabledScript = new TestScript('enabled-script', 'Enabled script');
        $disabledScript = new TestScript('disabled-script', 'Disabled script');

        $enabledCategory = new TestCategory(
            'Enabled Category',
            'This is enabled',
            '✅',
            1,
            true,
            [$enabledScript]
        );

        $disabledCategory = new TestCategory(
            'Disabled Category',
            'This is disabled',
            '❌',
            2,
            false,
            [$disabledScript]
        );

        $this->categoryService->registerCategory('enabled', $enabledCategory);
        $this->categoryService->registerCategory('disabled', $disabledCategory);

        // Test that only enabled categories are returned
        $enabledCategories = $this->categoryService->getEnabledCategories();
        expect($enabledCategories)->toHaveCount(1);
        expect($enabledCategories['enabled'])->toBe($enabledCategory);

        // Test that script search finds scripts from all categories (even disabled)
        $enabledScriptResults = $this->scriptService->search('enabled-script');
        $disabledScriptResults = $this->scriptService->search('disabled-script');

        expect($enabledScriptResults)->toHaveCount(1);
        expect($disabledScriptResults)->toHaveCount(1);
    });

    it('maintains proper script-category relationships', function () {
        $script1 = new TestScript('script-1', 'First script');
        $script2 = new TestScript('script-2', 'Second script');

        $category = new TestCategory('Test Category', 'Test', '🧪', 1, true, [$script1, $script2]);
        $this->categoryService->registerCategory('test', $category);

        // Test finding category by script name
        $foundCategory1 = $this->categoryService->getCategoryByScript('script-1');
        $foundCategory2 = $this->categoryService->getCategoryByScript('script-2');

        expect($foundCategory1)->toBe($category);
        expect($foundCategory2)->toBe($category);

        // Test that non-existent script returns null
        $notFound = $this->categoryService->getCategoryByScript('non-existent');
        expect($notFound)->toBeNull();
    });
});
