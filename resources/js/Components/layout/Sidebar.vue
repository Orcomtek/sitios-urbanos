<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    HomeIcon, 
    UsersIcon, 
    TableCellsIcon, 
    ArrowUpTrayIcon, 
    ChartBarIcon, 
    CurrencyDollarIcon, 
    ShoppingBagIcon,
    Bars3Icon,
    ClipboardIcon,
    KeyIcon
} from '@heroicons/vue/24/outline';

const iconMap: Record<string, any> = {
    'home': HomeIcon,
    'users': UsersIcon,
    'table': TableCellsIcon,
    'arrow-up-tray': ArrowUpTrayIcon,
    'chart-bar': ChartBarIcon,
    'currency-dollar': CurrencyDollarIcon,
    'shopping-bag': ShoppingBagIcon,
    'clipboard': ClipboardIcon,
    'key': KeyIcon,
};

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const navigationMenu = computed(() => (page.props as any).navigation_menu || []);

const getRouteUrl = (routeName: string) => {
    try {
        if (!communitySlug.value || routeName === '#') return '#';
        // @ts-ignore
        return route(routeName, { community_slug: communitySlug.value });
    } catch {
        return '#';
    }
};

const isActive = (routeName: string) => {
    const href = getRouteUrl(routeName);
    if (href === '#') return false;
    try {
        return page.url === new URL(href, 'http://localhost').pathname;
    } catch {
        return false;
    }
};
const role = computed(() => (page.props.tenant as any)?.role);
const dashboardRouteName = computed(() => {
    if (role.value === 'resident') {
        return 'tenant.resident.dashboard';
    }
    return 'tenant.admin.dashboard';
});
const dashboardHref = computed(() => getRouteUrl(dashboardRouteName.value));
const isActiveDashboard = computed(() => isActive(dashboardRouteName.value));
</script>

<template>
    <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
        <!-- Sidebar component -->
        <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white dark:bg-slate-900 dark:border-gray-800 pb-4 pt-5">
            <div class="flex flex-shrink-0 items-center px-4">
                <Link :href="dashboardHref" class="h-8 w-auto text-xl font-bold tracking-tight text-gray-900 dark:text-white hover:text-indigo-600 transition">
                    Sitios Urbanos
                </Link>
            </div>
            <div class="mt-8 flex flex-1 flex-col">
                <nav class="flex-1 space-y-6 px-2" aria-label="Sidebar">
                    <div>
                        <div class="space-y-1">
                            <Link
                                :href="dashboardHref"
                                class="group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActiveDashboard 
                                    ? 'bg-primary text-white hover:bg-primary/90' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                            >
                                <HomeIcon 
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                    :class="isActiveDashboard ? 'text-white' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                                    aria-hidden="true" 
                                />
                                Inicio
                            </Link>
                        </div>
                    </div>
                    <div v-for="group in navigationMenu" :key="group.title">
                        <h3 class="px-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                            {{ group.title }}
                        </h3>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items"
                                :key="item.key"
                                :href="getRouteUrl(item.route)"
                                class="group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActive(item.route) 
                                    ? 'bg-primary text-white hover:bg-primary/90' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                            >
                                <component 
                                    :is="iconMap[item.icon] || Bars3Icon" 
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                    :class="isActive(item.route) ? 'text-white' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                                    aria-hidden="true" 
                                />
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
