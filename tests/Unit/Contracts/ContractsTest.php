<?php

namespace NodiLabs\NodiShell\Tests\Unit\Contracts;

use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;
use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;
use NodiLabs\NodiShell\Tests\Fixtures\TestCategory;
use NodiLabs\NodiShell\Tests\Fixtures\TestScript;

describe('Contracts', function () {
    describe('ScriptInterface', function () {
        beforeEach(function () {
            $this->script = new TestScript(
                name: 'test-script',
                description: 'A test script for testing',
                tags: ['test', 'automation'],
                category: 'testing',
                productionSafe: true,
                preview: 'This is a preview',
                parameters: [
                    ['name' => 'param1', 'label' => 'Parameter 1', 'required' => true],
                    ['name' => 'param2', 'label' => 'Parameter 2', 'required' => false],
                ],
                executeResult: ['status' => 'success', 'data' => ['key' => 'value']]
            );
        });

        it('implements all required methods', function () {
            expect($this->script)->toBeInstanceOf(ScriptInterface::class);

            expect($this->script->getName())->toBe('test-script');
            expect($this->script->getDescription())->toBe('A test script for testing');
            expect($this->script->getTags())->toBe(['test', 'automation']);
            expect($this->script->getCategory())->toBe('testing');
            expect($this->script->isProductionSafe())->toBeTrue();
            expect($this->script->getPreview())->toBe('This is a preview');
            expect($this->script->getParameters())->toHaveCount(2);
        });

        it('can execute and return results', function () {
            $result = $this->script->execute(['param1' => 'value1']);

            expect($result)->toHaveKey('status');
            expect($result['status'])->toBe('success');
            expect($result)->toHaveKey('data');
        });

        it('handles various parameter configurations', function () {
            $parameters = $this->script->getParameters();

            expect($parameters[0]['name'])->toBe('param1');
            expect($parameters[0]['required'])->toBeTrue();
            expect($parameters[1]['name'])->toBe('param2');
            expect($parameters[1]['required'])->toBeFalse();
        });
    });

    describe('CategoryInterface', function () {
        beforeEach(function () {
            $this->script1 = new TestScript('script1');
            $this->script2 = new TestScript('script2');

            $this->category = new TestCategory(
                name: 'Test Category',
                description: 'A category for testing',
                icon: '🧪',
                sortOrder: 50,
                enabled: true,
                scripts: [$this->script1, $this->script2]
            );
        });

        it('implements all required methods', function () {
            expect($this->category)->toBeInstanceOf(CategoryInterface::class);

            expect($this->category->getName())->toBe('Test Category');
            expect($this->category->getDescription())->toBe('A category for testing');
            expect($this->category->getIcon())->toBe('🧪');
            expect($this->category->getSortOrder())->toBe(50);
            expect($this->category->isEnabled())->toBeTrue();
            expect($this->category->getScripts())->toHaveCount(2);
        });

        it('can manage scripts', function () {
            $scripts = $this->category->getScripts();

            expect($scripts[0])->toBe($this->script1);
            expect($scripts[1])->toBe($this->script2);
        });

        it('can be disabled', function () {
            $disabledCategory = new TestCategory(enabled: false);

            expect($disabledCategory->isEnabled())->toBeFalse();
        });
    });

    describe('SystemCheckInterface', function () {
        beforeEach(function () {
            $this->check = new class implements SystemCheckInterface
            {
                public function getLabel(): string
                {
                    return 'Test System Check';
                }

                public function run(): array
                {
                    return [
                        new CheckResultData(true, 'Test passed successfully'),
                        new CheckResultData(false, 'Test failed with error'),
                    ];
                }
            };
        });

        it('implements all required methods', function () {
            expect($this->check)->toBeInstanceOf(SystemCheckInterface::class);
            expect($this->check->getLabel())->toBe('Test System Check');
        });

        it('returns array of CheckResultData', function () {
            $results = $this->check->run();

            expect($results)->toHaveCount(2);
            expect($results[0])->toBeInstanceOf(CheckResultData::class);
            expect($results[1])->toBeInstanceOf(CheckResultData::class);

            expect($results[0]->successful)->toBeTrue();
            expect($results[0]->message)->toBe('Test passed successfully');

            expect($results[1]->successful)->toBeFalse();
            expect($results[1]->message)->toBe('Test failed with error');
        });
    });
});
