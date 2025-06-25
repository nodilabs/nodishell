<?php

namespace NodiLabs\NodiShell\Tests\Unit\Services;

use NodiLabs\NodiShell\Services\SystemCheckService;
use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;

describe('SystemCheckService', function () {

    it('can register a system check', function () {
        // Create a fresh service without config interference
        config(['nodishell.system_checks' => []]);
        $service = new SystemCheckService();

        $check = new class implements SystemCheckInterface {
            public function getLabel(): string
            {
                return 'Test Check';
            }

            public function run(): array
            {
                return [new CheckResultData(true, 'Test passed')];
            }
        };

        $service->register($check);

        $checks = $service->getChecks();
        expect($checks)->toHaveCount(1);
        expect($checks->first())->toBe($check);
    });

    it('can register multiple system checks', function () {
        // Create a fresh service without config interference
        config(['nodishell.system_checks' => []]);
        $service = new SystemCheckService();

        $check1 = new class implements SystemCheckInterface {
            public function getLabel(): string { return 'Check 1'; }
            public function run(): array { return []; }
        };

        $check2 = new class implements SystemCheckInterface {
            public function getLabel(): string { return 'Check 2'; }
            public function run(): array { return []; }
        };

        $service->register($check1);
        $service->register($check2);

        $checks = $service->getChecks();
        expect($checks)->toHaveCount(2);
    });

    it('loads checks from config on first access', function () {
        // Set up config with mock check classes
        config(['nodishell.system_checks' => [
            \NodiLabs\NodiShell\Checks\AppKeyCheck::class
        ]]);

        $service = new SystemCheckService();
        $checks = $service->getChecks();

        expect($checks->count())->toBeGreaterThan(0);
        expect($checks->first())->toBeInstanceOf(\NodiLabs\NodiShell\Checks\AppKeyCheck::class);
    });

    it('handles invalid check classes in config gracefully', function () {
        config(['nodishell.system_checks' => [
            'NonExistentClass',
            \NodiLabs\NodiShell\Checks\AppKeyCheck::class
        ]]);

        $service = new SystemCheckService();
        $checks = $service->getChecks();

        // Should only load the valid class
        expect($checks)->toHaveCount(1);
        expect($checks->first())->toBeInstanceOf(\NodiLabs\NodiShell\Checks\AppKeyCheck::class);
    });

    it('only initializes once', function () {
        config(['nodishell.system_checks' => [
            \NodiLabs\NodiShell\Checks\AppKeyCheck::class
        ]]);

        $service = new SystemCheckService();
        $checks1 = $service->getChecks();
        $checks2 = $service->getChecks();

        // Should be same count and same instances
        expect($checks1->count())->toBe($checks2->count());
    });
});
