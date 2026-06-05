<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    moveRequests: {
        type: Array,
        required: true
    }
});

const isManageModalOpen = ref(false);
const activeRequest = ref(null);

const currentTab = ref('Todos');
const tabs = ['Todos', 'Pendientes', 'Aprobados', 'Denegados', 'Modificados'];

const filteredRequests = computed(() => {
    if (currentTab.value === 'Todos') return props.moveRequests;
    const map = {
        'Pendientes': 'pending',
        'Aprobados': 'approved',
        'Denegados': 'denied',
        'Modificados': 'modified'
    };
    return props.moveRequests.filter(req => req.status === map[currentTab.value]);
});

const form = useForm({
    status: '',
    admin_notes: '',
    requested_date: '',
    start_time: '',
    end_time: '',
});

const openManageModal = (request) => {
    activeRequest.value = request;
    form.status = request.status === 'pending' ? 'approved' : request.status;
    form.admin_notes = request.admin_notes || '';
    form.requested_date = request.requested_date ? request.requested_date.split('T')[0] : '';
    form.start_time = request.start_time || '';
    form.end_time = request.end_time || '';
    isManageModalOpen.value = true;
};

const closeManageModal = () => {
    isManageModalOpen.value = false;
    activeRequest.value = null;
    form.reset();
    form.clearErrors();
};

const submitManagement = () => {
    form.put(route('tenant.admin.logistics.moves.update', { 
        community_slug: route().params.community_slug, 
        moveRequest: activeRequest.value.id 
    }), {
        preserveScroll: true,
        onSuccess: () => closeManageModal(),
    });
};

const translateType = (type) => {
    const types = {
        'move_in': 'Trasteo de Entrada',
        'move_out': 'Trasteo de Salida',
        'internal_transfer': 'Traslado Interno'
    };
    return types[type] || type;
};

const translateScale = (scale) => {
    const scales = {
        'light': 'Ligero',
        'medium': 'Medio',
        'heavy': 'Pesado'
    };
    return scales[scale] || scale;
};

const statusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-emerald-100 text-emerald-800',
        'denied': 'bg-red-100 text-red-800',
        'modified': 'bg-blue-100 text-blue-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const translateStatus = (status) => {
    const statuses = {
        'pending': 'Pendiente',
        'approved': 'Aprobado',
        'denied': 'Rechazado',
        'modified': 'Modificado'
    };
    return statuses[status] || status;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const cleanStr = dateStr.includes('T') ? dateStr.split('T')[0] + 'T12:00:00Z' : dateStr + 'T12:00:00Z';
        const date = new Date(cleanStr);
        return new Intl.DateTimeFormat('es-CO', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    } catch (e) {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Control de Mudanzas" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Control de Mudanzas y Traslados
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-slate-900">Solicitudes Activas e Histórico</h3>
                        </div>

                        <!-- TABS -->
                        <div class="mb-6 border-b border-slate-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab"
                                    @click="currentTab = tab"
                                    :class="[
                                        currentTab === tab
                                            ? 'border-emerald-600 text-emerald-600'
                                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                                    ]"
                                >
                                    {{ tab }}
                                </button>
                            </nav>
                        </div>

                        <!-- Data Table -->
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unidad</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Residente</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha Solicitada</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Escala</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                            {{ request.unit?.identifier || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ request.resident?.user?.name || request.resident?.full_name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ translateType(request.type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ formatDate(request.requested_date) }} <br>
                                            <span class="text-xs text-slate-400">{{ request.start_time }} - {{ request.end_time }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ translateScale(request.scale) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', statusBadgeClass(request.status)]">
                                                {{ translateStatus(request.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button 
                                                @click="openManageModal(request)"
                                                class="text-emerald-600 hover:text-emerald-900 focus:outline-none focus:underline"
                                            >
                                                Gestionar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredRequests.length === 0">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                            No hay solicitudes registradas.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Modal -->
        <Modal :show="isManageModalOpen" @close="closeManageModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 mb-4">
                    Gestionar Solicitud de Mudanza
                </h2>
                
                <div v-if="activeRequest" class="mb-6 bg-slate-50 p-4 rounded-md border border-slate-200">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">Residente</span>
                            <span class="text-slate-900">{{ activeRequest.resident?.user?.name || activeRequest.resident?.full_name || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Unidad</span>
                            <span class="text-slate-900">{{ activeRequest.unit?.identifier || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Fecha y Hora</span>
                            <span class="text-slate-900">{{ formatDate(activeRequest.requested_date) }} ({{ activeRequest.start_time }} - {{ activeRequest.end_time }})</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Escala</span>
                            <span class="text-slate-900">{{ translateScale(activeRequest.scale) }}</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitManagement">
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-slate-700">Resolución</label>
                        <select 
                            id="status" 
                            v-model="form.status" 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                        >
                            <option value="approved">Aprobar Solicitud</option>
                            <option value="denied">Rechazar Solicitud</option>
                            <option value="modified">Aprobar con Modificaciones</option>
                            <option value="pending" disabled>Pendiente</option>
                        </select>
                        <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                    </div>

                    <div v-if="form.status === 'modified'" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nueva Fecha</label>
                            <input type="date" v-model="form.requested_date" class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
                            <div v-if="form.errors.requested_date" class="text-red-500 text-xs mt-1">{{ form.errors.requested_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora Inicio</label>
                            <input type="time" v-model="form.start_time" class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
                            <div v-if="form.errors.start_time" class="text-red-500 text-xs mt-1">{{ form.errors.start_time }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora Fin</label>
                            <input type="time" v-model="form.end_time" class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
                            <div v-if="form.errors.end_time" class="text-red-500 text-xs mt-1">{{ form.errors.end_time }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="admin_notes" class="block text-sm font-medium text-slate-700">Notas de Administración (opcional)</label>
                        <textarea 
                            id="admin_notes" 
                            v-model="form.admin_notes" 
                            rows="3" 
                            class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                            placeholder="Detalles sobre instrucciones, puertas asignadas, etc."
                        ></textarea>
                        <div v-if="form.errors.admin_notes" class="text-red-500 text-xs mt-1">{{ form.errors.admin_notes }}</div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeManageModal" class="mr-3">
                            Cancelar
                        </SecondaryButton>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="form.processing"
                        >
                            Guardar Resolución
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
