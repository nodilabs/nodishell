<?php

namespace NodiLabs\NodiShell\Tests\Unit\Checks;

use NodiLabs\NodiShell\Checks\AppKeyCheck;
use NodiLabs\NodiShell\Data\CheckResultData;

describe('AppKeyCheck', function () {
    beforeEach(function () {
        $this->check = new AppKeyCheck;
    });

    it('has correct label', function () {
        expect($this->check->getLabel())->toBe('Application Key');
    });

    it('returns success when app key is set', function () {
        config(['app.key' => 'base64:test-key-here']);

        $results = $this->check->run();

        expect($results)->toHaveCount(1);
        expect($results[0])->toBeInstanceOf(CheckResultData::class);
        expect($results[0]->successful)->toBeTrue();
        expect($results[0]->message)->toBe('The application key is set.');
    });

    it('returns failure when app key is not set', function () {
        config(['app.key' => '']);

        $results = $this->check->run();

        expect($results)->toHaveCount(1);
        expect($results[0])->toBeInstanceOf(CheckResultData::class);
        expect($results[0]->successful)->toBeFalse();
        expect($results[0]->message)->toBe('The application key is not set. Please run `php artisan key:generate`.');
    });

    it('returns failure when app key is null', function () {
        config(['app.key' => null]);

        $results = $this->check->run();

        expect($results)->toHaveCount(1);
        expect($results[0]->successful)->toBeFalse();
    });
});
