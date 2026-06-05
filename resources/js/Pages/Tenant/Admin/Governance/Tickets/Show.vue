<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, nextTick } from 'vue';
import { ArrowLeftIcon, ChatBubbleLeftEllipsisIcon, EyeSlashIcon, UserCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    ticket: any;
    residentContext?: any;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const currentUser = computed(() => (page.props.auth as any).user);
const { show: showToast } = useToast();

const statusForm = useForm({
    status: props.ticket.status,
});

const replyForm = useForm({
    message: '',
});

const updateStatus = () => {
    statusForm.put(route('tenant.admin.governance.pqrs.status', { community_slug: communitySlug.value, ticket: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Estado actualizado exitosamente', 'success');
        }
    });
};

const sendReply = () => {
    replyForm.post(route('tenant.admin.governance.pqrs.reply', { community_slug: communitySlug.value, ticket: props.ticket.id }), {
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

const getRoleLabel = (roleKey: string) => {
    const map: Record<string, string> = {
        'owner': 'Propietario',
        'tenant': 'Inquilino Principal',
        'family_owner': 'Familiar (Propietario)',
        'family_tenant': 'Familiar (Inquilino)',
    };
    return map[roleKey] || roleKey || 'Desconocido';
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
                <Link :href="route('tenant.admin.governance.pqrs', { community_slug: communitySlug })" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ticket #{{ ticket.id.toString().padStart(4, '0') }}
                </h2>
                <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 border border-gray-200">
                    {{ getTypeLabel(ticket.type) }}
                </span>
                <span v-if="ticket.is_anonymous" class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-800 border border-slate-200">
                    <EyeSlashIcon class="w-3 h-3" /> Anónimo
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Columna 1 (span 2): Chat de Mensajes -->
                    <div class="lg:col-span-2 space-y-6">
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
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:bg-emerald-300">
                                        Enviar
                                    </button>
                                </form>
                                <div v-else class="text-center py-4">
                                    <p class="text-sm text-gray-500">Este ticket ha sido cerrado. Para reabrirlo, el residente debe enviar una solicitud de reapertura con un motivo o puede cambiar el estado manualmente en la gestión del ticket.</p>
                                </div>
                                <div v-if="replyForm.errors.message" class="text-xs text-red-600 mt-1">{{ replyForm.errors.message }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2 (span 1): Sidebar de Gestión y Contexto -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Contexto del Residente -->
                        <div class="bg-white shadow-sm border border-gray-100 rounded-lg p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Contexto del Residente</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center">
                                        <span class="text-slate-800 font-medium text-sm uppercase">
                                            {{ ticket.resident?.full_name?.substring(0, 2) || 'UN' }}
                                        </span>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="font-medium text-sm truncate" :class="{'text-gray-500 italic': ticket.is_anonymous, 'text-gray-900': !ticket.is_anonymous}">
                                            {{ ticket.resident?.full_name || 'Desconocido' }}
                                        </div>
                                        <div v-if="ticket.resident?.email" class="text-xs text-gray-500 truncate">{{ ticket.resident.email }}</div>
                                        <div v-if="ticket.resident?.phone" class="text-xs text-gray-500">{{ ticket.resident.phone }}</div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs text-gray-500 block mb-1">Unidad</label>
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                {{ ticket.unit?.identifier || 'Sin unidad' }}
                                            </span>
                                        </div>
                                        <div v-if="residentContext">
                                            <label class="text-xs text-gray-500 block mb-1">Rol</label>
                                            <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-700/10 text-center leading-tight">
                                                {{ getRoleLabel(residentContext.role) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="residentContext?.sponsor_name" class="border-t border-gray-100 pt-3">
                                    <p class="text-xs text-gray-500">Patrocinado por: <span class="font-medium text-gray-900">{{ residentContext.sponsor_name }}</span></p>
                                </div>

                                <div v-if="residentContext && !residentContext.is_active" class="border-t border-gray-100 pt-3">
                                    <div class="rounded-md bg-red-50 p-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <ExclamationTriangleIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
                                            </div>
                                            <div class="ml-2">
                                                <h3 class="text-sm font-medium text-red-800">Residente Inactivo</h3>
                                                <div class="mt-1 text-xs text-red-700">
                                                    <p>El autor de este ticket ya no tiene acceso activo a la unidad.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gestión del Ticket -->
                        <div class="bg-white shadow-sm border border-gray-100 rounded-lg p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Gestión del Ticket</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-xs text-gray-500">Estado</label>
                                    <select v-model="statusForm.status" @change="updateStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                        <option value="open">Abierto</option>
                                        <option value="in_progress">En Progreso</option>
                                        <option value="resolved">Resuelto</option>
                                        <option value="closed">Cerrado</option>
                                    </select>
                                    <div v-if="statusForm.errors.status" class="text-xs text-red-600 mt-1">{{ statusForm.errors.status }}</div>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Fecha de Creación</label>
                                    <div class="font-medium text-sm text-gray-900">{{ formatDate(ticket.created_at) }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
