<?php

namespace NodiLabs\NodiShell\Tests\Unit\Commands;

use NodiLabs\NodiShell\Commands\MakeScriptCommand;
use Illuminate\Filesystem\Filesystem;

describe('MakeScriptCommand', function () {
    beforeEach(function () {
        $this->files = new Filesystem();
        $this->command = new MakeScriptCommand($this->files);
    });

    afterEach(function () {
        // Clean up test files
        $testPaths = [
            app_path('Console/NodiShell/Scripts/TestScript.php'),
            app_path('Console/NodiShell/Scripts/MyCustomScript.php'),
            app_path('Console/NodiShell/Scripts/UserManagementScript.php'),
        ];

        foreach ($testPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    });

    it('automatically appends Script suffix if not provided', function () {
        $this->command->setLaravel(app());

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getNameInput');
        $method->setAccessible(true);

        // Mock the argument method
        $this->command = $this->getMockBuilder(MakeScriptCommand::class)
            ->setConstructorArgs([$this->files])
            ->onlyMethods(['argument'])
            ->getMock();

        $this->command->method('argument')
            ->with('name')
            ->willReturn('Test');

        $result = $method->invoke($this->command);
        expect($result)->toBe('TestScript');
    });

    it('preserves Script suffix if already provided', function () {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getNameInput');
        $method->setAccessible(true);

        $this->command = $this->getMockBuilder(MakeScriptCommand::class)
            ->setConstructorArgs([$this->files])
            ->onlyMethods(['argument'])
            ->getMock();

        $this->command->method('argument')
            ->with('name')
            ->willReturn('TestScript');

        $result = $method->invoke($this->command);
        expect($result)->toBe('TestScript');
    });

    it('has correct command signature', function () {
        expect($this->command->getName())->toBe('nodishell:script');
        expect($this->command->getDescription())->toBe('Create a new NodiShell script');
    });

    it('has correct stub path', function () {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getStub');
        $method->setAccessible(true);

        $stubPath = $method->invoke($this->command);
        expect($stubPath)->toContain('Stubs/nodishell.script.stub');
    });

    it('has correct namespace suffix', function () {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getNamespaceSuffix');
        $method->setAccessible(true);

        $suffix = $method->invoke($this->command);
        expect($suffix)->toBe('\\Console\\NodiShell\\Scripts');
    });

    it('generates correct custom replacements', function () {
        $this->command->setLaravel(app());

        // Mock the command with options
        $this->command = $this->getMockBuilder(MakeScriptCommand::class)
            ->setConstructorArgs([$this->files])
            ->onlyMethods(['option', 'ask', 'toTitleCase', 'toKebabCase'])
            ->getMock();

        $this->command->method('option')
            ->willReturnMap([
                ['description', 'Test script description'],
                ['category', 'test'],
                ['production-safe', true],
                ['tags', 'test,script,automation'],
            ]);

        $this->command->method('toTitleCase')
            ->willReturn('TestScript');

        $this->command->method('toKebabCase')
            ->willReturn('test-script');

        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getCustomReplacements');
        $method->setAccessible(true);

        $replacements = $method->invoke($this->command, 'TestScript');

        expect($replacements)->toHaveKey('scriptName');
        expect($replacements)->toHaveKey('description');
        expect($replacements)->toHaveKey('category');
        expect($replacements)->toHaveKey('productionSafe');
        expect($replacements)->toHaveKey('tags');
        expect($replacements['productionSafe'])->toBe('true');
    });
});
