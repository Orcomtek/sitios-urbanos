<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeftIcon, ChatBubbleLeftEllipsisIcon, EyeSlashIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    ticket: any;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const currentUser = computed(() => (page.props.auth as any).user);
const { show: showToast } = useToast();

const replyForm = useForm({
    message: '',
});

const isReopenModalOpen = ref(false);
const reopenForm = useForm({
    reason: '',
});

const confirmReopen = () => {
    reopenForm.post(route('tenant.resident.governance.pqrs.reopen', { community_slug: communitySlug.value, ticket: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            isReopenModalOpen.value = false;
            reopenForm.reset();
            showToast('Ticket reabierto exitosamente', 'success');
        }
    });
};

const sendReply = () => {
    replyForm.post(route('tenant.resident.governance.pqrs.reply', { community_slug: communitySlug.value, ticket: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            showToast('Respuesta enviada', 'success');
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
    <Head :title="'Ticket #' + ticket.id" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('tenant.resident.governance.pqrs', { community_slug: communitySlug })" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ticket #{{ ticket.id.toString().padStart(4, '0') }}
                </h2>
                <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 border border-gray-200">
                    {{ getTypeLabel(ticket.type) }}
                </span>
                <span v-if="ticket.is_anonymous" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                    <EyeSlashIcon class="w-3 h-3" /> Anónimo
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Detalle del Ticket -->
                    <div class="md:col-span-1 space-y-6">
                        <div class="bg-white shadow-sm border border-gray-100 rounded-lg p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Información General</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-xs text-gray-500">Estado</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="{
                                                'bg-yellow-50 text-yellow-800 ring-yellow-600/20': ticket.status === 'open',
                                                'bg-slate-50 text-slate-700 ring-slate-600/20': ticket.status === 'in_progress',
                                                'bg-green-50 text-green-700 ring-green-600/20': ticket.status === 'resolved',
                                                'bg-gray-50 text-gray-600 ring-gray-500/10': ticket.status === 'closed',
                                            }">
                                            {{ getStatusLabel(ticket.status) }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Unidad</label>
                                    <div class="font-medium text-sm text-gray-900">{{ ticket.unit?.identifier || 'Sin unidad' }}</div>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Fecha de Creación</label>
                                    <div class="font-medium text-sm text-gray-900">{{ formatDate(ticket.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat de Mensajes -->
                    <div class="md:col-span-2">
                        <div class="bg-white shadow-sm border border-gray-100 rounded-lg flex flex-col h-[600px]">
                            <!-- Asunto y Descripción Original -->
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-semibold text-gray-900">{{ ticket.subject }}</h3>
                                <p class="text-sm text-gray-700 mt-2 whitespace-pre-wrap">{{ ticket.description }}</p>
                            </div>

                            <!-- Lista de Respuestas -->
                            <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/30">
                                <div v-if="ticket.replies.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <ChatBubbleLeftEllipsisIcon class="w-12 h-12 mb-2 opacity-50" />
                                    <p class="text-sm">No hay respuestas en este ticket.</p>
                                </div>

                                <div v-for="reply in ticket.replies" :key="reply.id" class="flex gap-4" :class="reply.user_id === currentUser.id ? 'flex-row-reverse' : ''">
                                    <div class="flex-shrink-0">
                                        <UserCircleIcon class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <div class="max-w-[80%]" :class="reply.user_id === currentUser.id ? 'text-right' : ''">
                                        <div class="text-xs text-gray-500 mb-1">
                                            <span class="font-medium text-gray-700">{{ reply.user?.name || 'Usuario' }}</span>
                                            • {{ formatDate(reply.created_at) }}
                                        </div>
                                        <div class="rounded-2xl px-4 py-2 text-sm inline-block shadow-sm"
                                            :class="reply.user_id === currentUser.id ? 'bg-emerald-600 text-white rounded-tr-sm' : 'bg-white border border-gray-100 text-gray-800 rounded-tl-sm'">
                                            <span class="whitespace-pre-wrap">{{ reply.message }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario de Respuesta -->
                            <div class="p-4 border-t border-gray-100 bg-white rounded-b-lg">
                                <form v-if="['open', 'in_progress'].includes(ticket.status)" @submit.prevent="sendReply" class="flex gap-3">
                                    <textarea 
                                        v-model="replyForm.message" 
                                        rows="2" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm resize-none" 
                                        placeholder="Escribe una respuesta..."></textarea>
                                    <button 
                                        type="submit" 
                                        :disabled="replyForm.processing || !replyForm.message.trim()"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                                        Enviar
                                    </button>
                                </form>
                                <div v-else class="text-center py-4">
                                    <p class="text-sm text-gray-500 mb-3">Este ticket ha sido cerrado.</p>
                                    <button @click="isReopenModalOpen = true" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Reabrir Ticket
                                    </button>
                                </div>
                                <div v-if="replyForm.errors.message" class="text-xs text-red-600 mt-1">{{ replyForm.errors.message }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <Modal :show="isReopenModalOpen" @close="isReopenModalOpen = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Reabrir Ticket
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Por favor, indique el motivo por el cual necesita reabrir este ticket.
                </p>

                <div class="mt-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Motivo de la reapertura</label>
                    <textarea
                        id="reason"
                        v-model="reopenForm.reason"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm resize-none"
                        placeholder="Explique el motivo..."
                    ></textarea>
                    <div v-if="reopenForm.errors.reason" class="text-xs text-red-600 mt-1">{{ reopenForm.errors.reason }}</div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="isReopenModalOpen = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                        Cancelar
                    </button>
                    <button
                        @click="confirmReopen"
                        :disabled="reopenForm.processing || !reopenForm.reason.trim()"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:bg-emerald-300"
                    >
                        Confirmar Reapertura
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
