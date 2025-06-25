<?php

namespace NodiLabs\NodiShell\Tests\Unit\Data;

use NodiLabs\NodiShell\Data\CheckResultData;

describe('CheckResultData', function () {
    it('can create a successful check result', function () {
        $result = new CheckResultData(
            successful: true,
            message: 'Test passed'
        );

        expect($result->successful)->toBeTrue();
        expect($result->message)->toBe('Test passed');
    });

    it('can create a failed check result', function () {
        $result = new CheckResultData(
            successful: false,
            message: 'Test failed'
        );

        expect($result->successful)->toBeFalse();
        expect($result->message)->toBe('Test failed');
    });

    it('has readonly properties', function () {
        $result = new CheckResultData(
            successful: true,
            message: 'Original message'
        );

        expect($result)->toHaveProperty('successful');
        expect($result)->toHaveProperty('message');
    });
});
