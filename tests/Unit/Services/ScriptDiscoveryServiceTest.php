<?php

namespace NodiLabs\NodiShell\Tests\Unit\Services;

use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;
use NodiLabs\NodiShell\Services\CategoryDiscoveryService;
use NodiLabs\NodiShell\Services\ScriptDiscoveryService;

describe('ScriptDiscoveryService', function () {
    beforeEach(function () {
        $this->categoryService = new CategoryDiscoveryService;
        $this->service = new ScriptDiscoveryService($this->categoryService);

        // Create mock scripts
        $this->script1 = new class implements ScriptInterface
        {
            public function getName(): string
            {
                return 'user-create';
            }

            public function getDescription(): string
            {
                return 'Create a new user';
            }

            public function getTags(): array
            {
                return ['user', 'create'];
            }

            public function getCategory(): string
            {
                return 'users';
            }

            public function isProductionSafe(): bool
            {
                return false;
            }

            public function execute(array $parameters): mixed
            {
                return null;
            }

            public function getParameters(): array
            {
                return [];
            }
        };

        $this->script2 = new class implements ScriptInterface
        {
            public function getName(): string
            {
                return 'user-delete';
            }

            public function getDescription(): string
            {
                return 'Delete a user';
            }

            public function getTags(): array
            {
                return ['user', 'delete'];
            }

            public function getCategory(): string
            {
                return 'users';
            }

            public function isProductionSafe(): bool
            {
                return false;
            }

            public function execute(array $parameters): mixed
            {
                return null;
            }

            public function getParameters(): array
            {
                return [];
            }
        };

        $this->script3 = new class implements ScriptInterface
        {
            public function getName(): string
            {
                return 'cache-clear';
            }

            public function getDescription(): string
            {
                return 'Clear application cache';
            }

            public function getTags(): array
            {
                return ['cache', 'clear'];
            }

            public function getCategory(): string
            {
                return 'maintenance';
            }

            public function isProductionSafe(): bool
            {
                return true;
            }

            public function execute(array $parameters): mixed
            {
                return null;
            }

            public function getParameters(): array
            {
                return [];
            }
        };

        // Create mock categories with scripts
        $userCategory = new class($this->script1, $this->script2) implements CategoryInterface
        {
            public function __construct(private $script1, private $script2) {}

            public function getName(): string
            {
                return 'Users';
            }

            public function getDescription(): string
            {
                return 'User management';
            }

            public function getIcon(): string
            {
                return '👥';
            }

            public function getSortOrder(): int
            {
                return 1;
            }

            public function isEnabled(): bool
            {
                return true;
            }

            public function getScripts(): array
            {
                return [$this->script1, $this->script2];
            }
        };

        $maintenanceCategory = new class($this->script3) implements CategoryInterface
        {
            public function __construct(private $script3) {}

            public function getName(): string
            {
                return 'Maintenance';
            }

            public function getDescription(): string
            {
                return 'System maintenance';
            }

            public function getIcon(): string
            {
                return '🔧';
            }

            public function getSortOrder(): int
            {
                return 2;
            }

            public function isEnabled(): bool
            {
                return true;
            }

            public function getScripts(): array
            {
                return [$this->script3];
            }
        };

        $this->categoryService->registerCategory('users', $userCategory);
        $this->categoryService->registerCategory('maintenance', $maintenanceCategory);
    });

    it('can search scripts by name', function () {
        $results = $this->service->search('user');

        expect($results)->toHaveCount(2);

        $scriptNames = $results->map(fn ($script) => $script->getName())->toArray();
        expect($scriptNames)->toContain('user-create');
        expect($scriptNames)->toContain('user-delete');
    });

    it('can search scripts with partial matches', function () {
        $results = $this->service->search('create');

        expect($results)->toHaveCount(1);
        expect($results->first()->getName())->toBe('user-create');
    });

    it('returns empty collection when no matches found', function () {
        $results = $this->service->search('nonexistent');

        expect($results)->toHaveCount(0);
    });

    it('can find script by exact id', function () {
        $script = $this->service->findById('cache-clear');

        expect($script)->not()->toBeNull();
        expect($script->getName())->toBe('cache-clear');
    });

    it('returns null when script id not found', function () {
        $script = $this->service->findById('nonexistent-script');

        expect($script)->toBeNull();
    });

    it('search is case sensitive', function () {
        $results = $this->service->search('USER');

        expect($results)->toHaveCount(0);
    });

    it('flattens scripts from all categories', function () {
        // This is testing the private getScripts method indirectly
        $allUserScripts = $this->service->search('user');
        $allCacheScripts = $this->service->search('cache');

        expect($allUserScripts)->toHaveCount(2);
        expect($allCacheScripts)->toHaveCount(1);
    });

    it('caches scripts after first access', function () {
        // First search should build the cache
        $results1 = $this->service->search('user');
        expect($results1)->toHaveCount(2);

        // Add a new category with script (this won't be found since it's cached)
        $newScript = new class implements ScriptInterface
        {
            public function getName(): string
            {
                return 'user-update';
            }

            public function getDescription(): string
            {
                return 'Update user';
            }

            public function getTags(): array
            {
                return ['user'];
            }

            public function getCategory(): string
            {
                return 'users';
            }

            public function isProductionSafe(): bool
            {
                return false;
            }

            public function execute(array $parameters): mixed
            {
                return null;
            }

            public function getParameters(): array
            {
                return [];
            }
        };

        $newCategory = new class($newScript) implements CategoryInterface
        {
            public function __construct(private $newScript) {}

            public function getName(): string
            {
                return 'New Users';
            }

            public function getDescription(): string
            {
                return 'New user management';
            }

            public function getIcon(): string
            {
                return '👤';
            }

            public function getSortOrder(): int
            {
                return 3;
            }

            public function isEnabled(): bool
            {
                return true;
            }

            public function getScripts(): array
            {
                return [$this->newScript];
            }
        };

        $this->categoryService->registerCategory('new-users', $newCategory);

        // Second search should use cache, so still 2 results
        $results2 = $this->service->search('user');
        expect($results2)->toHaveCount(2);
    });
});
