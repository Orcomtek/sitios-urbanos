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
        'seguridad' => 'Seguridad',
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
        'access' => [
            'name' => 'Mis Accesos',
            'icon' => 'key',
            'route' => 'tenant.resident.access.index',
            'roles' => ['resident'],
            'category' => 'operativo',
        ],
        'resident_moves' => [
            'name' => 'Mudanzas',
            'icon' => 'truck',
            'route' => 'tenant.resident.logistics.moves.index',
            'roles' => ['resident'],
            'category' => 'operativo',
        ],
        'admin_moves' => [
            'name' => 'Mudanzas',
            'icon' => 'TruckIcon',
            'route' => 'tenant.admin.logistics.moves.index',
            'roles' => ['tenant_admin', 'sub_admin'],
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
            'name' => 'Mis PQRS',
            'icon' => 'clipboard',
            'route' => 'tenant.resident.governance.pqrs',
            'roles' => ['resident'],
            'category' => 'gobernanza',
        ],
        'pqrs_admin' => [
            'name' => 'Gestión PQRS',
            'icon' => 'clipboard-document-list',
            'route' => 'tenant.admin.governance.pqrs.index',
            'roles' => ['tenant_admin', 'sub_admin'],
            'category' => 'gobernanza',
        ],
        'radar' => [
            'name' => 'Radar de Seguridad',
            'icon' => 'shield-check',
            'route' => 'tenant.admin.security.radar.index',
            'roles' => ['tenant_admin', 'sub_admin', 'guard'],
            'category' => 'seguridad',
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
