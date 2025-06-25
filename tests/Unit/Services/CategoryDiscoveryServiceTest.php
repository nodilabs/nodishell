<?php

namespace NodiLabs\NodiShell\Tests\Unit\Services;

use NodiLabs\NodiShell\Services\CategoryDiscoveryService;
use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;

describe('CategoryDiscoveryService', function () {
    beforeEach(function () {
        $this->service = new CategoryDiscoveryService();
    });

    it('can register a category manually', function () {
        $category = new class implements CategoryInterface {
            public function getName(): string { return 'Test Category'; }
            public function getDescription(): string { return 'Test Description'; }
            public function getIcon(): string { return '🧪'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $this->service->registerCategory('test', $category);

        expect($this->service->getCategory('test'))->toBe($category);
    });

    it('can get all categories', function () {
        $category1 = new class implements CategoryInterface {
            public function getName(): string { return 'Category 1'; }
            public function getDescription(): string { return 'Description 1'; }
            public function getIcon(): string { return '🧪'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $category2 = new class implements CategoryInterface {
            public function getName(): string { return 'Category 2'; }
            public function getDescription(): string { return 'Description 2'; }
            public function getIcon(): string { return '⚡'; }
            public function getSortOrder(): int { return 2; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $this->service->registerCategory('cat1', $category1);
        $this->service->registerCategory('cat2', $category2);

        $categories = $this->service->getAllCategories();
        expect($categories)->toHaveCount(2);
        expect($categories['cat1'])->toBe($category1);
        expect($categories['cat2'])->toBe($category2);
    });

    it('returns null for non-existent category', function () {
        expect($this->service->getCategory('non-existent'))->toBeNull();
    });

    it('filters enabled categories only', function () {
        $enabledCategory = new class implements CategoryInterface {
            public function getName(): string { return 'Enabled'; }
            public function getDescription(): string { return 'Description'; }
            public function getIcon(): string { return '✅'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $disabledCategory = new class implements CategoryInterface {
            public function getName(): string { return 'Disabled'; }
            public function getDescription(): string { return 'Description'; }
            public function getIcon(): string { return '❌'; }
            public function getSortOrder(): int { return 2; }
            public function isEnabled(): bool { return false; }
            public function getScripts(): array { return []; }
        };

        $this->service->registerCategory('enabled', $enabledCategory);
        $this->service->registerCategory('disabled', $disabledCategory);

        $enabledCategories = $this->service->getEnabledCategories();
        expect($enabledCategories)->toHaveCount(1);
        expect($enabledCategories['enabled'])->toBe($enabledCategory);
    });

    it('sorts categories by sort order', function () {
        $category1 = new class implements CategoryInterface {
            public function getName(): string { return 'Category 1'; }
            public function getDescription(): string { return 'Description 1'; }
            public function getIcon(): string { return '🧪'; }
            public function getSortOrder(): int { return 3; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $category2 = new class implements CategoryInterface {
            public function getName(): string { return 'Category 2'; }
            public function getDescription(): string { return 'Description 2'; }
            public function getIcon(): string { return '⚡'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $category3 = new class implements CategoryInterface {
            public function getName(): string { return 'Category 3'; }
            public function getDescription(): string { return 'Description 3'; }
            public function getIcon(): string { return '🚀'; }
            public function getSortOrder(): int { return 2; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $this->service->registerCategory('cat1', $category1);
        $this->service->registerCategory('cat2', $category2);
        $this->service->registerCategory('cat3', $category3);

        $sortedCategories = $this->service->getCategoriesSorted();
        $values = array_values($sortedCategories);

        expect($values[0])->toBe($category2); // sort order 1
        expect($values[1])->toBe($category3); // sort order 2
        expect($values[2])->toBe($category1); // sort order 3
    });

    it('can find category by script name', function () {
        $script = new class implements ScriptInterface {
            public function getName(): string { return 'test-script'; }
            public function getDescription(): string { return 'Test'; }
            public function getTags(): array { return []; }
            public function getCategory(): string { return 'test'; }
            public function isProductionSafe(): bool { return true; }
            public function getPreview(): ?string { return null; }
            public function execute(array $parameters): mixed { return null; }
            public function getParameters(): array { return []; }
        };

        $category = new class($script) implements CategoryInterface {
            public function __construct(private $script) {}
            public function getName(): string { return 'Test Category'; }
            public function getDescription(): string { return 'Description'; }
            public function getIcon(): string { return '🧪'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return [$this->script]; }
        };

        $this->service->registerCategory('test', $category);

        $foundCategory = $this->service->getCategoryByScript('test-script');
        expect($foundCategory)->toBe($category);
    });

    it('returns null when script not found in any category', function () {
        $category = new class implements CategoryInterface {
            public function getName(): string { return 'Empty Category'; }
            public function getDescription(): string { return 'Description'; }
            public function getIcon(): string { return '🧪'; }
            public function getSortOrder(): int { return 1; }
            public function isEnabled(): bool { return true; }
            public function getScripts(): array { return []; }
        };

        $this->service->registerCategory('empty', $category);

        $foundCategory = $this->service->getCategoryByScript('non-existent-script');
        expect($foundCategory)->toBeNull();
    });
});
