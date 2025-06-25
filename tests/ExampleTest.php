<?php

namespace NodiLabs\NodiShell\Tests;

it('can load the package', function () {
    expect(class_exists(\NodiLabs\NodiShell\NodiShell::class))->toBeTrue();
    expect(class_exists(\NodiLabs\NodiShell\NodiShellServiceProvider::class))->toBeTrue();
});
