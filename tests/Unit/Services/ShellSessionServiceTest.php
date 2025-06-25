<?php

namespace NodiLabs\NodiShell\Tests\Unit\Services;

use NodiLabs\NodiShell\Services\ShellSessionService;

describe('ShellSessionService', function () {
    beforeEach(function () {
        $this->service = new ShellSessionService();
    });

    describe('History Management', function () {
        it('can add commands to history', function () {
            $this->service->addToHistory('command1');
            $this->service->addToHistory('command2');

            expect($this->service->getHistory())->toBe(['command1', 'command2']);
        });

        it('maintains history limit', function () {
            $service = new ShellSessionService();

            // Add 102 commands (exceeding the default limit of 100)
            for ($i = 1; $i <= 102; $i++) {
                $service->addToHistory("command{$i}");
            }

            $history = $service->getHistory();
            expect(count($history))->toBe(100);
            expect($history[0])->toBe('command3'); // First two should be removed
            expect($history[99])->toBe('command102');
        });

        it('can clear history', function () {
            $this->service->addToHistory('command1');
            $this->service->addToHistory('command2');

            $this->service->clearHistory();

            expect($this->service->getHistory())->toBe([]);
        });
    });

    describe('Variable Management', function () {
        it('can set and get variables', function () {
            $this->service->setVariable('name', 'John');
            $this->service->setVariable('age', 30);

            expect($this->service->getVariable('name'))->toBe('John');
            expect($this->service->getVariable('age'))->toBe(30);
        });

        it('returns null for non-existent variables', function () {
            expect($this->service->getVariable('non_existent'))->toBeNull();
        });

        it('can check if variable exists', function () {
            $this->service->setVariable('name', 'John');

            expect($this->service->hasVariable('name'))->toBeTrue();
            expect($this->service->hasVariable('non_existent'))->toBeFalse();
        });

        it('can handle null values', function () {
            $this->service->setVariable('null_var', null);

            expect($this->service->hasVariable('null_var'))->toBeTrue();
            expect($this->service->getVariable('null_var'))->toBeNull();
        });

        it('can remove variables', function () {
            $this->service->setVariable('name', 'John');
            expect($this->service->hasVariable('name'))->toBeTrue();

            $this->service->removeVariable('name');
            expect($this->service->hasVariable('name'))->toBeFalse();
        });

        it('can get all variables', function () {
            $this->service->setVariable('name', 'John');
            $this->service->setVariable('age', 30);

            $variables = $this->service->getAllVariables();
            expect($variables)->toBe(['name' => 'John', 'age' => 30]);
        });

        it('can clear all variables', function () {
            $this->service->setVariable('name', 'John');
            $this->service->setVariable('age', 30);

            $this->service->clearVariables();

            expect($this->service->getAllVariables())->toBe([]);
        });

        it('can handle various data types', function () {
            $this->service->setVariable('string', 'test');
            $this->service->setVariable('integer', 42);
            $this->service->setVariable('float', 3.14);
            $this->service->setVariable('boolean', true);
            $this->service->setVariable('array', [1, 2, 3]);
            $this->service->setVariable('object', (object) ['key' => 'value']);

            expect($this->service->getVariable('string'))->toBe('test');
            expect($this->service->getVariable('integer'))->toBe(42);
            expect($this->service->getVariable('float'))->toBe(3.14);
            expect($this->service->getVariable('boolean'))->toBeTrue();
            expect($this->service->getVariable('array'))->toBe([1, 2, 3]);
            expect($this->service->getVariable('object'))->toEqual((object) ['key' => 'value']);
        });
    });

    describe('Context Management', function () {
        it('can set and get context', function () {
            $this->service->setContext('current_user', 123);
            $this->service->setContext('environment', 'testing');

            expect($this->service->getContext('current_user'))->toBe(123);
            expect($this->service->getContext('environment'))->toBe('testing');
        });

        it('returns null for non-existent context', function () {
            expect($this->service->getContext('non_existent'))->toBeNull();
        });

        it('can get all context', function () {
            $this->service->setContext('user', 123);
            $this->service->setContext('env', 'test');

            $context = $this->service->getAllContext();
            expect($context)->toBe(['user' => 123, 'env' => 'test']);
        });

        it('can clear context', function () {
            $this->service->setContext('user', 123);

            $this->service->clearContext();

            expect($this->service->getAllContext())->toBe([]);
        });
    });

    describe('Reset Functionality', function () {
        it('can reset all data', function () {
            $this->service->addToHistory('command1');
            $this->service->setVariable('name', 'John');
            $this->service->setContext('user', 123);

            $this->service->reset();

            expect($this->service->getHistory())->toBe([]);
            expect($this->service->getAllVariables())->toBe([]);
            expect($this->service->getAllContext())->toBe([]);
        });
    });
});
