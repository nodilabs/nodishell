<?php

namespace NodiLabs\NodiShell\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NodiLabs\NodiShell\NodiShell
 */
class NodiShell extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NodiLabs\NodiShell\NodiShell::class;
    }
}
