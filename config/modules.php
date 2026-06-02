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
        'configuracion' => 'Configuración',
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
            'name' => 'Unidades',
            'icon' => 'home',
            'route' => 'tenant.admin.core.units.index',
            'roles' => ['tenant_admin', 'sub_admin'],
            'category' => 'operativo',
        ],
        'residents' => [
            'name' => 'Residentes',
            'icon' => 'users',
            'route' => 'tenant.admin.core.residents.index',
            'roles' => ['tenant_admin', 'sub_admin'],
            'category' => 'operativo',
        ],
        'census' => [
            'name' => 'Mi Censo',
            'icon' => 'users',
            'route' => 'tenant.resident.census.index',
            'roles' => ['resident'],
            'category' => 'operativo',
        ],
        'units_generator' => [
            'name' => 'Generador de Matriz',
            'icon' => 'table',
            'route' => 'tenant.admin.core.units.generator',
            'roles' => ['tenant_admin'],
            'category' => 'operativo',
        ],
        'imports' => [
            'name' => 'Importar Datos',
            'icon' => 'arrow-up-tray',
            'route' => 'tenant.admin.core.imports.index',
            'roles' => ['tenant_admin'],
            'category' => 'operativo',
        ],
        'polls' => [
            'name' => 'Votaciones',
            'icon' => 'chart-bar',
            'route' => '#', 
            'roles' => ['tenant_admin', 'sub_admin', 'resident'],
            'category' => 'gobernanza',
        ],
        'pqrs' => [
            'name' => 'PQRS',
            'icon' => 'clipboard',
            'route' => 'tenant.resident.governance.pqrs',
            'roles' => ['resident'],
            'category' => 'gobernanza',
        ],
        'finance' => [
            'name' => 'Finanzas',
            'icon' => 'currency-dollar',
            'route' => '#', 
            'roles' => ['tenant_admin', 'sub_admin', 'accountant', 'resident'],
            'category' => 'finanzas',
        ],
        'providers' => [
            'name' => 'Proveedores',
            'icon' => 'users',
            'route' => 'tenant.admin.ecosystem.providers', 
            'roles' => ['tenant_admin', 'sub_admin', 'resident'],
            'category' => 'ecosistema',
        ],
        'marketplace' => [
            'name' => 'Marketplace',
            'icon' => 'shopping-bag',
            'route' => 'tenant.resident.ecosystem.index',
            'roles' => ['tenant_admin', 'resident'],
            'category' => 'ecosistema',
        ],
        'team' => [
            'name' => 'Equipo',
            'icon' => 'users',
            'route' => 'tenant.admin.settings.team.index',
            'roles' => ['tenant_admin'],
            'category' => 'configuracion',
        ],
    ],
];
