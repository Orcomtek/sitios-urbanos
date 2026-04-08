<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const tenantRole = computed(() => (page.props.tenant as any)?.role as string | null);
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const navigation = computed(() => {
    const items = [];
    
    // Everyone sees some basic modules potentially, maybe Units/Residents for Admin
    if (tenantRole.value === 'admin') {
        items.push({ name: 'Unidades', href: route('units.index', { community_slug: communitySlug.value }) });
        items.push({ name: 'Residentes', href: route('residents.index', { community_slug: communitySlug.value }) });
    }

    // Cockpit Navigation for Admins and Guards
    if (tenantRole.value === 'admin' || tenantRole.value === 'guard') {
        items.push({ name: 'Panel Operativo', href: route('tenant.cockpit.dashboard', { community_slug: communitySlug.value }) });
        items.push({ name: 'Cola de Trabajo', href: route('tenant.cockpit.work-queue', { community_slug: communitySlug.value }) });
    }
    
    // Cockpit for Admins only
    if (tenantRole.value === 'admin') {
        items.push({ name: 'Cola Administrativa', href: route('tenant.cockpit.admin-work-queue', { community_slug: communitySlug.value }) });
    }

    // Cockpit for Residents only
    if (tenantRole.value === 'resident') {
        items.push({ name: 'Cabina del Residente', href: route('tenant.cockpit.resident', { community_slug: communitySlug.value }) });
    }

    return items;
});

const isActive = (href: string) => {
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
        <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white pb-4 pt-5">
            <div class="flex flex-shrink-0 items-center px-4">
                <div class="h-8 w-auto text-xl font-bold tracking-tight text-gray-900">
                    Sitios Urbanos
                </div>
            </div>
            <div class="mt-8 flex flex-1 flex-col">
                <nav class="flex-1 space-y-1 bg-white px-2" aria-label="Sidebar">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                        :class="{'bg-gray-100 text-gray-900': isActive(item.href)}"
                    >
                        {{ item.name }}
                    </Link>
                </nav>
            </div>
        </div>
    </div>
</template>
