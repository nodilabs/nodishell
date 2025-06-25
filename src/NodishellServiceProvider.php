<?php

namespace NodiLabs\NodiShell;

use NodiLabs\NodiShell\Commands\BaseNodiShellGeneratorCommand;
use NodiLabs\NodiShell\Commands\MakeCategoryCommand;
use NodiLabs\NodiShell\Commands\MakeCheckCommand;
use NodiLabs\NodiShell\Commands\MakeScriptCommand;
use NodiLabs\NodiShell\Commands\NodiShellCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NodiShellServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nodishell')
            ->hasConfigFile()
            ->hasCommands(
                [
                    NodiShellCommand::class,
                    BaseNodiShellGeneratorCommand::class,
                    MakeCategoryCommand::class,
                    MakeScriptCommand::class,
                    MakeCheckCommand::class,
                ]
            );
    }
}
