<?php

namespace NodiLabs\NodiShell\Checks;

use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;

class AppKeyCheck implements SystemCheckInterface
{
    public function getLabel(): string
    {
        return 'Application Key';
    }

    public function run(): array
    {
        $keyIsSet = ! empty(config('app.key'));

        return [
            new CheckResultData(
                successful: $keyIsSet,
                message: $keyIsSet ? 'The application key is set.' : 'The application key is not set. Please run `php artisan key:generate`.',
            ),
        ];
    }
}
