<?php

namespace NodiLabs\NodiShell\Contracts;

use NodiLabs\NodiShell\Data\CheckResultData;

interface SystemCheckInterface
{
    public function getLabel(): string;

    /**
     * @return CheckResultData[]
     */
    public function run(): array;
}
