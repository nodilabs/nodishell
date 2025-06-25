<?php

namespace NodiLabs\NodiShell\Data;

class CheckResultData
{
    public function __construct(
        public readonly bool $successful,
        public readonly string $message,
    ) {}
}
