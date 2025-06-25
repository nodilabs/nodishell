<?php

namespace NodiLabs\NodiShell\Services;

use Illuminate\Database\Eloquent\Model;

final class AutocompleteService
{
    public function getAvailableModels(): array
    {
        return collect(app('modules'))
            ->map(fn ($module) => $module->getModels())
            ->flatten()
            ->filter(fn ($model) => $model instanceof Model)
            ->map(fn ($model) => $model->getTable())
            ->unique()
            ->toArray();
    }
}
