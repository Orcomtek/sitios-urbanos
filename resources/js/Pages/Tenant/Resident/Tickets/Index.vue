<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { PencilIcon, TrashIcon, ChatBubbleLeftRightIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import TicketFormModal from './Components/TicketFormModal.vue';

const props = defineProps<{
    tickets: Array<any>;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const isFormOpen = ref(false);
const editTicket = ref<any>(null);

const openForm = (ticket: any = null) => {
    editTicket.value = ticket;
    isFormOpen.value = true;
};

const closeForm = () => {
    isFormOpen.value = false;
    editTicket.value = null;
};

// Deletion Logic
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const ticketToDelete = ref<number | null>(null);

const confirmDelete = (id: number) => {
    ticketToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!ticketToDelete.value) return;
    isDeleting.value = true;
    
    router.delete(route('tenant.resident.governance.pqrs.destroy', { community_slug: communitySlug.value, ticket: ticketToDelete.value }), {
        onSuccess: () => {
            showToast('Ticket eliminado exitosamente', 'success');
            isDeleteModalOpen.value = false;
            ticketToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

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
    <Head title="Mis PQRS" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mis PQRS
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center space-x-3">
                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                                <ChatBubbleLeftRightIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold leading-none tracking-tight">Historial de Solicitudes</h3>
                                <p class="text-sm text-gray-500 mt-1">Administra tus Peticiones, Quejas, Reclamos y Sugerencias.</p>
                            </div>
                        </div>
                        <button @click="openForm()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Crear Ticket
                        </button>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asunto</th>
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
                                        <div class="text-sm text-gray-900 font-medium">{{ ticket.subject }}</div>
                                        <div class="text-sm text-gray-500 line-clamp-1 max-w-xs">{{ ticket.description }}</div>
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
                                            <button @click="openForm(ticket)" class="text-gray-400 hover:text-indigo-600 transition"><PencilIcon class="w-5 h-5" /></button>
                                            <button @click="confirmDelete(ticket.id)" class="text-gray-400 hover:text-red-600 transition"><TrashIcon class="w-5 h-5" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="tickets.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No has creado ningún ticket de PQRS.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <TicketFormModal :show="isFormOpen" :ticket="editTicket" @close="closeForm" />

        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            :processing="isDeleting"
            @close="isDeleteModalOpen = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
