<?php

namespace App\Services;

use App\Enums\CommunityRole;
use App\Models\Community;

class ModuleRegistryService
{
    /**
     * Get the authorized navigation modules for the current tenant and user role.
     *
     * @param  CommunityRole|string|null  $role
     */
    public function getAuthorizedModules(?Community $community, $role): array
    {
        if (! $community || ! $role) {
            return [];
        }

        $registry = config('modules.registry', []);
        $groups = config('modules.groups', []);

        $saasSettings = $community->saas_settings ?? [];
        $activeModules = $saasSettings['active_modules'] ?? [];

        $roleValue = $role instanceof CommunityRole ? $role->value : $role;

        $authorized = [];

        foreach ($registry as $key => $module) {
            // Check if the module is active in the community's saas_settings
            if (! in_array($key, $activeModules, true)) {
                // Depending on requirements, we might want to bypass this for core modules.
                // But following architectural ruling strictly: check active_modules.
                continue;
            }

            // Check if user has the role required for the module
            if (! in_array($roleValue, $module['roles'] ?? [], true)) {
                continue;
            }

            $category = $module['category'];
            $groupId = $category;

            if (! isset($authorized[$groupId])) {
                $authorized[$groupId] = [
                    'title' => $groups[$category] ?? ucfirst($category),
                    'items' => [],
                ];
            }

            $authorized[$groupId]['items'][] = [
                'name' => $module['name'],
                'icon' => $module['icon'] ?? null,
                'route' => $module['route'],
                'key' => $key,
            ];
        }

        // Return array values so it's a list of groups in JSON
        return array_values($authorized);
    }
}
