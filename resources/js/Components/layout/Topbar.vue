<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import NotificationDropdown from '@/Components/Notifications/NotificationDropdown.vue';
import Dropdown from '@/Components/Dropdown.vue';

const page = usePage();
const user = (page.props.auth as any)?.user;
const initials = user?.name ? user.name.substring(0, 1).toUpperCase() : 'U';

const currentCommunity = (page.props.tenant as any)?.community;
const currentRole = (page.props.tenant as any)?.role;
const authorizedCommunities = (page.props.tenant as any)?.authorized_communities || [];

const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const roleLabels: Record<string, string> = {
    'admin': 'Admin',
    'resident': 'Residente',
    'guard': 'Guardia',
};

const displayRole = computed(() => {
    return currentRole ? (roleLabels[currentRole] || currentRole) : '';
});
</script>

<template>
    <div
        class="sticky top-0 z-10 flex h-16 flex-shrink-0 border-b border-gray-200 bg-white shadow-sm"
    >
        <button
            type="button"
            class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 md:hidden"
        >
            <span class="sr-only">Abrir menú</span>
            <!-- Hamburger icon placeholder -->
            <svg
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
            </svg>
        </button>
        <div class="flex flex-1 justify-between px-4">
            <div class="flex flex-1 items-center">
                <Dropdown v-if="currentCommunity" align="left" width="48">
                    <template #trigger>
                        <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                            <div class="flex flex-col items-start">
                                <span class="font-bold text-gray-900">{{ currentCommunity.name }}</span>
                                <span v-if="displayRole" class="text-xs text-gray-500">{{ displayRole }}</span>
                            </div>
                            <svg class="-mr-0.5 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <div class="px-4 py-2 text-xs text-gray-500 font-semibold border-b border-gray-100">
                            Mis Comunidades
                        </div>
                        <template v-for="community in authorizedCommunities" :key="community.id">
                            <a :href="route('tenant.dashboard', { community_slug: community.slug })" 
                               class="block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out focus:outline-none"
                               :class="currentCommunity.id === community.id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100'">
                                <div>{{ community.name }}</div>
                                <div class="text-xs" :class="currentCommunity.id === community.id ? 'text-indigo-500' : 'text-gray-500'">
                                    {{ roleLabels[community.role] || community.role }}
                                </div>
                            </a>
                        </template>
                    </template>
                </Dropdown>
            </div>
            <div class="ml-4 flex items-center md:ml-6">
                <NotificationDropdown />
                
                <!-- Profile dropdown placeholder -->
                <div class="relative ml-3">
                    <div>
                        <button
                            type="button"
                            class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            id="user-menu-button"
                            aria-expanded="false"
                            aria-haspopup="true"
                            @click="toggleMenu"
                        >
                            <span class="sr-only">Abrir menú de usuario</span>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 font-medium text-indigo-700 hover:bg-indigo-200"
                            >
                                {{ initials }}
                            </div>
                        </button>
                    </div>

                    <!-- Dropdown menu -->
                    <div
                        v-if="isMenuOpen"
                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="user-menu-button"
                        tabindex="-1"
                    >
                        <div class="px-4 py-2 border-b border-gray-100 mb-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                        </div>
                        <Link
                            :href="route('tenant.logout', { community_slug: currentCommunity?.slug })"
                            method="post"
                            as="button"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            role="menuitem"
                            tabindex="-1"
                        >
                            Cerrar sesión
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
