<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;
const userRole = (page.props.auth as any)?.user?.roles?.[0]?.name || 'resident'; // approx

const userId = (page.props.auth as any)?.user?.id;

const notifications = computed(() => (page.props.unreadNotifications as any[]) || []);
const isMenuOpen = ref(false);

const unreadCount = computed(() => {
    if (notifications.value.length > 0) {
        return notifications.value.filter((n: any) => !n.read_at).length;
    }
    return (page.props.unreadNotificationsCount as number) || 0;
});

const loadNotifications = async () => {
    try {
        const response = await axios.get(route('tenant.cockpit.notifications.index', { community_slug: communitySlug }));
        // Only update if we absolutely need to, or just trigger an Inertia reload:
        router.reload({ only: ['unreadNotifications', 'unreadNotificationsCount'] });
    } catch (error) {
        console.error('Error loading notifications', error);
    }
};

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const debouncedLoadNotifications = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        loadNotifications();
    }, 300);
};

onMounted(() => {
    // REMOVED loadNotifications() on mount to prevent overriding Inertia props with empty state
    document.addEventListener('click', closeMenu);
    
    // @ts-ignore
    if (userId && window.Echo) {
        // @ts-ignore
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification: any) => {
                debouncedLoadNotifications();
            });
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
    // @ts-ignore
    if (userId && window.Echo) {
        // @ts-ignore
        window.Echo.leave(`App.Models.User.${userId}`);
    }
    if (debounceTimer) clearTimeout(debounceTimer);
});

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
    // 1. Bulletproof context check (Where is the user clicking from?)
    const isAdminPanel = window.location.pathname.includes('/admin');

    // 2. Robust ticket detection
    const ticketId = notification.data?.ticket_id || notification.data?.id;
    const isTicket = ticketId || notification.type?.includes('Ticket') || notification.data?.type?.includes('pqrs');

    if (isTicket) {
        if (isAdminPanel) {
            return ticketId 
                ? route('tenant.admin.governance.pqrs.show', { community_slug: communitySlug, ticket: ticketId })
                : route('tenant.admin.governance.pqrs.index', { community_slug: communitySlug });
        } else {
            return ticketId 
                ? route('tenant.resident.governance.pqrs.show', { community_slug: communitySlug, ticket: ticketId })
                : route('tenant.resident.governance.pqrs.index', { community_slug: communitySlug });
        }
    }

    // 3. Fallbacks
    return isAdminPanel
        ? route('tenant.admin.dashboard', { community_slug: communitySlug })
        : route('tenant.resident.dashboard', { community_slug: communitySlug });
};

const markAsRead = (notification: any) => {
    if (!notification.read_at) {
        router.patch(route('tenant.cockpit.notifications.read', {
            community_slug: communitySlug,
            id: notification.id
        }), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                const targetUrl = getTargetRoute(notification);
                if (targetUrl) {
                    isMenuOpen.value = false;
                    router.visit(targetUrl);
                }
            }
        });
    } else {
        const targetUrl = getTargetRoute(notification);
        if (targetUrl) {
            isMenuOpen.value = false;
            router.visit(targetUrl);
        }
    }
};

const markAllAsRead = () => {
    router.patch(route('tenant.cockpit.notifications.read-all', { community_slug: communitySlug }), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};


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
                                {{ notification.data.title || notification.data.subject || 'Notificación' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                {{ notification.data.message || notification.data.description || '' }}
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
