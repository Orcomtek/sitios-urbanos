<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const navigationMenu = computed(() => (page.props as any).navigation_menu || []);

const getRouteUrl = (routeName: string) => {
    try {
        if (!communitySlug.value) return '#';
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
</script>

<template>
    <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
        <!-- Sidebar component -->
        <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white dark:bg-slate-900 dark:border-gray-800 pb-4 pt-5">
            <div class="flex flex-shrink-0 items-center px-4">
                <div class="h-8 w-auto text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Sitios Urbanos
                </div>
            </div>
            <div class="mt-8 flex flex-1 flex-col">
                <nav class="flex-1 space-y-6 px-2" aria-label="Sidebar">
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
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
