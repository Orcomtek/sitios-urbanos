<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
    KeyIcon,
    TruckIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';
import RestrictedModuleOverlay from '@/Components/ui/RestrictedModuleOverlay.vue';
import RestrictionModal from '@/Components/ui/RestrictionModal.vue';

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
    'truck': TruckIcon,
    'TruckIcon': TruckIcon,
    'BanknotesIcon': BanknotesIcon,
};

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const navigationMenu = computed(() => (page.props as any).navigation_menu || []);

const getRouteUrl = (routeName: string) => {
    if (!routeName || routeName === '#') return '#';
    try {
        // @ts-ignore
        const slug = route().params.community_slug || communitySlug.value;
        // @ts-ignore
        return route(routeName, { community_slug: slug });
    } catch (error) {
        console.error(`Ziggy failed to compile route: ${routeName}`, error);
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
const dunning = computed(() => (page.props as any).dunning ?? { is_restricted: false, restricted_modules: [] });
const restrictedModules = computed(() => dunning.value.restricted_modules as string[]);

const showRestrictionModal = ref(false);

const isModuleRestricted = (moduleKey: string) =>
    role.value === 'resident' && restrictedModules.value.includes(moduleKey);
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
                            <template v-for="item in group.items" :key="item.key">
                                <RestrictedModuleOverlay
                                    :is-restricted="isModuleRestricted(item.key)"
                                    @click-restricted="showRestrictionModal = true"
                                >
                                    <Link
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
                                </RestrictedModuleOverlay>
                            </template>

                            <!-- DIRECT DOM INJECTION FOR ADMIN MOVES -->
                            <Link
                                v-if="group.title === 'Operativo' && role !== 'resident'"
                                :href="getRouteUrl('tenant.admin.logistics.moves.index')"
                                class="group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActive('tenant.admin.logistics.moves.index') 
                                    ? 'bg-primary text-white hover:bg-primary/90' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                            >
                                <TruckIcon 
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                    :class="isActive('tenant.admin.logistics.moves.index') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                                    aria-hidden="true" 
                                />
                                Mudanzas
                            </Link>

                            <!-- DIRECT DOM INJECTION FOR LEDGER -->
                            <Link
                                v-if="group.title === 'Finanzas' && role !== 'resident'"
                                :href="getRouteUrl('tenant.admin.financial.ledger.index')"
                                class="group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActive('tenant.admin.financial.ledger.index') 
                                    ? 'bg-primary text-white hover:bg-primary/90' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                            >
                                <CurrencyDollarIcon 
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                    :class="isActive('tenant.admin.financial.ledger.index') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                                    aria-hidden="true" 
                                />
                                Cartera
                            </Link>

                        </div>
                    </div>

                    <!-- DIRECT DOM INJECTION FOR RESIDENT STATEMENT -->
                    <div v-if="role === 'resident'">
                        <h3 class="px-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 mt-6">
                            Finanzas
                        </h3>
                        <div class="space-y-1">
                            <!-- Financial statement is NEVER restricted — it is the payment destination -->
                            <Link
                                :href="getRouteUrl('tenant.resident.financial.statement.index')"
                                class="group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActive('tenant.resident.financial.statement.index') 
                                    ? 'bg-primary text-white hover:bg-primary/90' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                            >
                                <CurrencyDollarIcon 
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                    :class="isActive('tenant.resident.financial.statement.index') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                                    aria-hidden="true" 
                                />
                                Mis Finanzas
                            </Link>
                        </div>
                    </div>

                </nav>
            </div>
        </div>
    </div>

    <!-- Restriction Modal (Loss Aversion CRO) -->
    <RestrictionModal
        :show="showRestrictionModal"
        :total-overdue="dunning.total_overdue"
        :oldest-due-date="dunning.oldest_due_date"
        @close="showRestrictionModal = false"
    />
</template>
