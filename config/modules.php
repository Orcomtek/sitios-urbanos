<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Groups
    |--------------------------------------------------------------------------
    |
    | Defines the groups used to categorize modules in the navigation sidebar.
    |
    */
    'groups' => [
        'operativo' => 'Operativo',
        'gobernanza' => 'Gobernanza',
        'finanzas' => 'Finanzas',
        'ecosistema' => 'Ecosistema',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Registry
    |--------------------------------------------------------------------------
    |
    | A dictionary defining available SaaS modules, their semantic routes,
    | required roles, and their category group.
    |
    */
    'registry' => [
        'units' => [
            'name' => 'Unidades y Residentes',
            'icon' => 'home',
            'route' => 'tenant.admin.core.units.index',
            'roles' => ['admin'],
            'category' => 'operativo',
        ],
        'polls' => [
            'name' => 'Votaciones',
            'icon' => 'chart-bar',
            'route' => 'tenant.admin.dashboard', // Fallback for now
            'roles' => ['admin', 'resident'],
            'category' => 'gobernanza',
        ],
        'finance' => [
            'name' => 'Finanzas',
            'icon' => 'currency-dollar',
            'route' => 'tenant.admin.dashboard', // Fallback for now
            'roles' => ['admin', 'resident'],
            'category' => 'finanzas',
        ],
        'providers' => [
            'name' => 'Proveedores',
            'icon' => 'users',
            'route' => 'tenant.admin.ecosystem.providers', // Admins use ecosystem too, resident might have a different route, let's use the standard one
            'roles' => ['admin', 'resident'],
            'category' => 'ecosistema',
        ],
        'marketplace' => [
            'name' => 'Marketplace',
            'icon' => 'shopping-bag',
            'route' => 'tenant.resident.ecosystem.index',
            'roles' => ['admin', 'resident'],
            'category' => 'ecosistema',
        ],
    ],
];
