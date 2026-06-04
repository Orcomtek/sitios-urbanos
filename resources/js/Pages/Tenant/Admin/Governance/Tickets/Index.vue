<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { EyeIcon, EyeSlashIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    tickets: Array<any>;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const getTypeLabel = (value: string) => {
    const map: Record<string, string> = {
        peticion: 'Petición',
        queja: 'Queja',
        reclamo: 'Reclamo',
        sugerencia: 'Sugerencia'
    };
    return map[value] || value;
};

const getStatusLabel = (value: string) => {
    const map: Record<string, string> = {
        open: 'Abierto',
        in_progress: 'En Progreso',
        resolved: 'Resuelto',
        closed: 'Cerrado'
    };
    return map[value] || value;
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Intl.DateTimeFormat('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(dateString));
};
</script>

<template>
    <Head title="Gestión de PQRS" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de PQRS
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center space-x-3">
                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                                <ClipboardDocumentListIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold leading-none tracking-tight">Solicitudes de Residentes</h3>
                                <p class="text-sm text-gray-500 mt-1">Administra y responde Peticiones, Quejas, Reclamos y Sugerencias de la comunidad.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asunto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente / Unidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ticket in tickets" :key="ticket.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ ticket.id.toString().padStart(4, '0') }}</div>
                                        <div class="text-xs text-gray-500">{{ formatDate(ticket.created_at) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span v-if="ticket.has_unread_admin" class="relative flex h-3 w-3 flex-shrink-0">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                            </span>
                                            <div class="text-sm text-gray-900" :class="ticket.has_unread_admin ? 'font-semibold' : 'font-medium'">{{ ticket.subject }}</div>
                                        </div>
                                        <div class="text-sm text-gray-500 line-clamp-1 max-w-xs mt-1">{{ ticket.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium" :class="ticket.is_anonymous ? 'text-gray-500 italic' : 'text-gray-900'">
                                            {{ ticket.resident?.full_name || 'Desconocido' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ ticket.unit?.identifier || 'Sin unidad' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <span>{{ getTypeLabel(ticket.type) }}</span>
                                            <span v-if="ticket.is_anonymous" class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                                <EyeSlashIcon class="w-3 h-3" /> Anónimo
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="{
                                                'bg-yellow-50 text-yellow-800 ring-yellow-600/20': ticket.status === 'open',
                                                'bg-blue-50 text-blue-700 ring-blue-600/20': ticket.status === 'in_progress',
                                                'bg-green-50 text-green-700 ring-green-600/20': ticket.status === 'resolved',
                                                'bg-gray-50 text-gray-600 ring-gray-500/10': ticket.status === 'closed',
                                            }">
                                            {{ getStatusLabel(ticket.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3">
                                            <Link :href="route('tenant.admin.governance.pqrs.show', { community_slug: communitySlug, ticket: ticket.id })" class="relative group text-gray-400 hover:text-indigo-600 transition" aria-label="Ver Detalles">
                                                <EyeIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Ver Detalles</span>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="tickets.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No hay tickets de PQRS registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
