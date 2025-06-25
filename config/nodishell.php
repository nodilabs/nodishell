<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Branding Configuration
    |--------------------------------------------------------------------------
    |
    | Customize the appearance and branding of your NodiShell instance.
    |
    */
    'branding' => [
        'title' => env('NODISHELL_TITLE', '🚀 NodiShell'),
        'subtitle' => env('NODISHELL_SUBTITLE', 'Advanced Laravel Interactive Shell'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific NodiShell features.
    |
    */
    'features' => [
        'search' => env('NODISHELL_ENABLE_SEARCH', true),
        'raw_php' => env('NODISHELL_ENABLE_RAW_PHP', true),
        'variable_manager' => env('NODISHELL_ENABLE_VARIABLES', true),
        'system_status' => env('NODISHELL_ENABLE_SYSTEM_STATUS', true),
        'model_explorer' => env('NODISHELL_ENABLE_MODEL_EXPLORER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Safety Configuration
    |--------------------------------------------------------------------------
    |
    | Configure safety features for production environments.
    |
    */
    'production_safety' => [
        'safe_mode' => env('NODISHELL_SAFE_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery Configuration
    |--------------------------------------------------------------------------
    |
    | Configure paths for auto-discovering categories, scripts, and system checks.
    |
    */
    'discovery' => [
        'categories_path' => app_path('Console/NodiShell/Categories'),
        'scripts_path' => app_path('Console/NodiShell/Scripts'),
        'checks_path' => null, // app_path('Console/NodiShell/Checks'), // use the app_path to enable auto-discovery on system checks
    ],

    /*
    |--------------------------------------------------------------------------
    | Manual System Checks Registration
    |--------------------------------------------------------------------------
    |
    | Manually register system check classes here if you prefer not to use
    | auto-discovery or need to register checks from other locations.
    |
    */
    'system_checks' => [
        \NodiLabs\NodiShell\Checks\AppKeyCheck::class,
    ],
];
