<?php

namespace NodiLabs\NodiShell\Tests\Unit\Services;

use NodiLabs\NodiShell\Services\ShellSessionService;

describe('ShellSessionService', function () {
    describe('History Management', function () {
        it('can add commands to history', function () {
            $service = new ShellSessionService;
            $service->addToHistory('command1');
            $service->addToHistory('command2');

            expect($service->getHistory())->toBe(['command1', 'command2']);
        });

        it('maintains history limit', function () {
            $service = new ShellSessionService;

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
            $service = new ShellSessionService;
            $service->addToHistory('command1');
            $service->addToHistory('command2');

            $service->clearHistory();

            expect($service->getHistory())->toBe([]);
        });
    });

    describe('Variable Management', function () {
        it('can set and get variables', function () {
            $service = new ShellSessionService;
            $service->setVariable('name', 'John');
            $service->setVariable('age', 30);

            expect($service->getVariable('name'))->toBe('John');
            expect($service->getVariable('age'))->toBe(30);
        });

        it('returns null for non-existent variables', function () {
            $service = new ShellSessionService;
            expect($service->getVariable('non_existent'))->toBeNull();
        });

        it('can check if variable exists', function () {
            $service = new ShellSessionService;
            $service->setVariable('name', 'John');

            expect($service->hasVariable('name'))->toBeTrue();
            expect($service->hasVariable('non_existent'))->toBeFalse();
        });

        it('can handle null values', function () {
            $service = new ShellSessionService;
            $service->setVariable('null_var', null);

            expect($service->hasVariable('null_var'))->toBeTrue();
            expect($service->getVariable('null_var'))->toBeNull();
        });

        it('can remove variables', function () {
            $service = new ShellSessionService;
            $service->setVariable('name', 'John');
            expect($service->hasVariable('name'))->toBeTrue();

            $service->removeVariable('name');
            expect($service->hasVariable('name'))->toBeFalse();
        });

        it('can get all variables', function () {
            $service = new ShellSessionService;
            $service->setVariable('name', 'John');
            $service->setVariable('age', 30);

            $variables = $service->getAllVariables();
            expect($variables)->toBe(['name' => 'John', 'age' => 30]);
        });

        it('can clear all variables', function () {
            $service = new ShellSessionService;
            $service->setVariable('name', 'John');
            $service->setVariable('age', 30);

            $service->clearVariables();

            expect($service->getAllVariables())->toBe([]);
        });

        it('can handle various data types', function () {
            $service = new ShellSessionService;
            $service->setVariable('string', 'test');
            $service->setVariable('integer', 42);
            $service->setVariable('float', 3.14);
            $service->setVariable('boolean', true);
            $service->setVariable('array', [1, 2, 3]);
            $service->setVariable('object', (object) ['key' => 'value']);

            expect($service->getVariable('string'))->toBe('test');
            expect($service->getVariable('integer'))->toBe(42);
            expect($service->getVariable('float'))->toBe(3.14);
            expect($service->getVariable('boolean'))->toBeTrue();
            expect($service->getVariable('array'))->toBe([1, 2, 3]);
            expect($service->getVariable('object'))->toEqual((object) ['key' => 'value']);
        });
    });

    describe('Context Management', function () {
        it('can set and get context', function () {
            $service = new ShellSessionService;
            $service->setContext('current_user', 123);
            $service->setContext('environment', 'testing');

            expect($service->getContext('current_user'))->toBe(123);
            expect($service->getContext('environment'))->toBe('testing');
        });

        it('returns null for non-existent context', function () {
            $service = new ShellSessionService;
            expect($service->getContext('non_existent'))->toBeNull();
        });

        it('can get all context', function () {
            $service = new ShellSessionService;
            $service->setContext('user', 123);
            $service->setContext('env', 'test');

            $context = $service->getAllContext();
            expect($context)->toBe(['user' => 123, 'env' => 'test']);
        });

        it('can clear context', function () {
            $service = new ShellSessionService;
            $service->setContext('user', 123);

            $service->clearContext();

            expect($service->getAllContext())->toBe([]);
        });
    });

    describe('Reset Functionality', function () {
        it('can reset all data', function () {
            $service = new ShellSessionService;
            $service->addToHistory('command1');
            $service->setVariable('name', 'John');
            $service->setContext('user', 123);

            $service->reset();

            expect($service->getHistory())->toBe([]);
            expect($service->getAllVariables())->toBe([]);
            expect($service->getAllContext())->toBe([]);
        });
    });
});
