<?php

namespace NodiLabs\NodiShell\Tests\Feature;

use NodiLabs\NodiShell\NodiShellServiceProvider;
use NodiLabs\NodiShell\Services\CategoryDiscoveryService;
use NodiLabs\NodiShell\Services\ScriptDiscoveryService;
use NodiLabs\NodiShell\Services\ShellSessionService;
use NodiLabs\NodiShell\Services\SystemCheckService;
use NodiLabs\NodiShell\Checks\AppKeyCheck;

describe('NodiShell Package', function () {
    it('registers all required services', function () {
        expect(app()->bound(CategoryDiscoveryService::class))->toBeTrue();
        expect(app()->bound(ScriptDiscoveryService::class))->toBeTrue();
        expect(app()->bound(ShellSessionService::class))->toBeTrue();
        expect(app()->bound(SystemCheckService::class))->toBeTrue();
    });

    it('can resolve services from container', function () {
        $categoryService = app(CategoryDiscoveryService::class);
        $scriptService = app(ScriptDiscoveryService::class);
        $sessionService = app(ShellSessionService::class);
        $systemCheckService = app(SystemCheckService::class);

        expect($categoryService)->toBeInstanceOf(CategoryDiscoveryService::class);
        expect($scriptService)->toBeInstanceOf(ScriptDiscoveryService::class);
        expect($sessionService)->toBeInstanceOf(ShellSessionService::class);
        expect($systemCheckService)->toBeInstanceOf(SystemCheckService::class);
    });

    it('loads configuration correctly', function () {
        expect(config('nodishell'))->not()->toBeNull();
        expect(config('nodishell.features'))->toBeArray();
        expect(config('nodishell.production_safety'))->toBeArray();
        expect(config('nodishell.discovery'))->toBeArray();
    });

    it('registers commands', function () {
        $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
        $commands = $kernel->all();

        expect($commands)->toHaveKey('nodishell');
        expect($commands)->toHaveKey('nodishell:script');
        expect($commands)->toHaveKey('nodishell:category');
        expect($commands)->toHaveKey('nodishell:check');
    });

    it('has default system checks configured', function () {
        $systemChecks = config('nodishell.system_checks', []);

        expect($systemChecks)->toContain(AppKeyCheck::class);
    });

    it('can execute system checks through service', function () {
        $systemCheckService = app(SystemCheckService::class);
        $checks = $systemCheckService->getChecks();

        expect($checks->count())->toBeGreaterThan(0);

        // Test that we can run the checks
        $appKeyCheck = $checks->first(fn($check) => $check instanceof AppKeyCheck);
        expect($appKeyCheck)->not()->toBeNull();

        $results = $appKeyCheck->run();
        expect($results)->toBeArray();
        expect(count($results))->toBeGreaterThan(0);
    });

    it('has proper directory structure configuration', function () {
        $config = config('nodishell.discovery');

        expect($config['categories_path'])->toContain('Console/NodiShell/Categories');
        expect($config['scripts_path'])->toContain('Console/NodiShell/Scripts');
    });

    it('service provider provides correct services', function () {
        $provider = new NodiShellServiceProvider(app());

        // Test that the provider is properly configured
        expect($provider)->toBeInstanceOf(NodiShellServiceProvider::class);
    });

    it('can access features configuration', function () {
        $features = config('nodishell.features');

        expect($features)->toHaveKey('search');
        expect($features)->toHaveKey('raw_php');
        expect($features)->toHaveKey('variable_manager');
        expect($features)->toHaveKey('system_status');
        expect($features)->toHaveKey('model_explorer');
    });
});
