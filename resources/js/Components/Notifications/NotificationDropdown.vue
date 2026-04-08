<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;
const userRole = (page.props.auth as any)?.user?.roles?.[0]?.name || 'resident'; // approx

const notifications = ref<any[]>([]);
const isMenuOpen = ref(false);

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const loadNotifications = async () => {
    try {
        const response = await axios.get(route('api.cockpit.notifications.index', { community_slug: communitySlug }));
        notifications.value = response.data.data;
    } catch (error) {
        console.error('Error loading notifications', error);
    }
};

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = (e: MouseEvent) => {
    const el = document.getElementById('notification-dropdown-container');
    if (el && !el.contains(e.target as Node)) {
        isMenuOpen.value = false;
    }
};

const getTargetRoute = (notification: any) => {
    const type = notification.data.type;
    switch (type) {
        case 'package_received':
        case 'visitor_registered':
        case 'invitation_consumed':
            return route('tenant.cockpit.resident.operations', { community_slug: communitySlug });
        case 'pqrs_updated':
            return route('tenant.cockpit.resident.pqrs', { community_slug: communitySlug });
        case 'pqrs_created':
            return route('tenant.cockpit.admin-work-queue', { community_slug: communitySlug });
        case 'payment_confirmed':
        case 'payment_failed':
            return route('tenant.cockpit.resident', { community_slug: communitySlug });
        case 'emergency_triggered':
            return route('tenant.cockpit.work-queue', { community_slug: communitySlug });
        default:
            return route('tenant.cockpit.dashboard', { community_slug: communitySlug });
    }
};

const markAsRead = async (notification: any) => {
    if (!notification.read_at) {
        try {
            await axios.patch(route('api.cockpit.notifications.read', {
                community_slug: communitySlug,
                id: notification.id
            }));
            notification.read_at = new Date().toISOString();
        } catch (error) {}
    }
    
    const targetUrl = getTargetRoute(notification);
    if (targetUrl) {
        isMenuOpen.value = false;
        router.visit(targetUrl);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.patch(route('api.cockpit.notifications.read-all', { community_slug: communitySlug }));
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
    } catch (error) {}
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
    if (communitySlug) {
        loadNotifications();
    }
    document.addEventListener('click', closeMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
});
</script>

<template>
    <div class="relative ml-2" id="notification-dropdown-container">
        <!-- Notification Bell Trigger -->
        <button
            type="button"
            class="relative flex rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            @click="toggleMenu"
        >
            <span class="sr-only">Ver notificaciones</span>
            <!-- Heroicon bell -->
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            
            <!-- Unread Badge -->
            <span v-if="unreadCount > 0" class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-600 text-[10px] font-bold text-white leading-tight flex items-center justify-center transform translate-x-1 -translate-y-1 ring-2 ring-white">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Panel -->
        <div
            v-if="isMenuOpen"
            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        >
            <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-medium text-gray-900">Notificaciones</h3>
                <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Marcas todas</button>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                    No tienes notificaciones
                </div>
                
                <template v-else>
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="markAsRead(notification)"
                        class="w-full text-left px-4 py-3 border-b border-gray-50 last:border-b-0 hover:bg-gray-50 flex items-start space-x-3"
                        :class="{'bg-blue-50/50': !notification.read_at}"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium" :class="!notification.read_at ? 'text-gray-900' : 'text-gray-600'">
                                {{ notification.data.title }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                {{ notification.data.message }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                {{ formatDate(notification.created_at) }}
                            </p>
                        </div>
                        <div v-if="!notification.read_at" class="flex-shrink-0 mt-1">
                            <span class="block h-2 w-2 rounded-full bg-indigo-600"></span>
                        </div>
                    </button>
                </template>
            </div>
            <div class="px-4 py-2 border-t border-gray-100 text-center sticky bottom-0 bg-white">
                <p class="text-xs text-gray-500">Mostrando últimas {{ notifications.length }} notificaciones</p>
            </div>
        </div>
    </div>
</template>
