<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-binding Paths
    |--------------------------------------------------------------------------
    |
    | Define the paths where the package should scan for interfaces and
    | their implementations. The package will automatically bind
    | interfaces to their corresponding implementations.
    |
    */
    'paths' => [
        'contracts' => [
            'repositories' => app_path('Contracts/Repositories'),
            'services' => app_path('Contracts/Services'),
        ],
        'implementations' => [
            'repositories' => app_path('Repositories'),
            'services' => app_path('Services'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming Conventions
    |--------------------------------------------------------------------------
    |
    | Define the naming conventions used for automatic binding.
    | The package will use these patterns to match interfaces
    | with their implementations.
    |
    */
    'naming' => [
        'interface_suffix' => 'Interface',
        'remove_segments' => ['Contracts'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-binding
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic binding of interfaces to implementations.
    | When enabled, the package will automatically scan and bind interfaces
    | during the service provider registration.
    |
    */
    'auto_binding' => [
        'enabled' => true,
        'cache_bindings' => true,
    ],
];
