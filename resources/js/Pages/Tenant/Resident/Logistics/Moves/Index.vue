<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { store as storeMoveRequest } from '@/routes/tenant/resident/logistics/moves';

const props = defineProps({
    moveRequests: {
        type: Array,
        required: true,
    },
    residents: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const communitySlug = page.props.tenant?.community?.slug || '';

const form = useForm({
    resident_id: props.residents.length === 1 ? props.residents[0].id : '',
    type: '',
    requested_date: '',
    start_time: '',
    end_time: '',
    scale: '',
});

const submit = () => {
    form.post(storeMoveRequest.url(communitySlug), {
        preserveScroll: true,
        onSuccess: () => form.reset('type', 'requested_date', 'start_time', 'end_time', 'scale'),
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'approved': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'denied': return 'bg-red-100 text-red-800 border-red-200';
        case 'modified': return 'bg-orange-100 text-orange-800 border-orange-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'pending': return 'Pendiente';
        case 'approved': return 'Aprobado';
        case 'denied': return 'Rechazado';
        case 'modified': return 'Modificado';
        default: return status;
    }
};

const getTypeText = (type) => {
    switch (type) {
        case 'move_in': return 'Ingreso';
        case 'move_out': return 'Salida';
        case 'internal_transfer': return 'Traslado Interno';
        default: return type;
    }
};

const getScaleText = (scale) => {
    switch (scale) {
        case 'light': return 'Pequeña/Cajas';
        case 'medium': return 'Mediana/Mobiliario';
        case 'heavy': return 'Grande/Trasteo Completo';
        default: return scale;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const cleanStr = dateStr.includes('T') ? dateStr.split('T')[0] + 'T12:00:00Z' : dateStr + 'T12:00:00Z';
        const date = new Date(cleanStr);
        return new Intl.DateTimeFormat('es-CO', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    } catch { return dateStr; }
};

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    try {
        const [hour, min] = timeStr.split(':');
        const date = new Date();
        date.setHours(hour, min);
        return new Intl.DateTimeFormat('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true }).format(date);
    } catch { return timeStr; }
};

</script>

<template>
    <AppLayout title="Mudanzas y Logística">
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Autorizaciones de Mudanza
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Form Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-slate-900">Solicitar Autorización</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Por favor ingrese los detalles de su mudanza para coordinar la logística y la protección de áreas comunes.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Unidad -->
                                <div v-if="residents.length > 1">
                                    <label for="resident_id" class="block text-sm font-medium text-slate-700">Unidad</label>
                                    <select id="resident_id" v-model="form.resident_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                        <option value="" disabled>Seleccione una unidad</option>
                                        <option v-for="resident in residents" :key="resident.id" :value="resident.id">
                                            {{ resident.unit?.identifier }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.resident_id" class="text-red-500 text-xs mt-1">{{ form.errors.resident_id }}</div>
                                </div>

                                <!-- Tipo de Movimiento -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-slate-700">Tipo de Movimiento</label>
                                    <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                        <option value="" disabled>Seleccione el tipo</option>
                                        <option value="move_in">Ingreso</option>
                                        <option value="move_out">Salida</option>
                                        <option value="internal_transfer">Traslado Interno</option>
                                    </select>
                                    <div v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</div>
                                </div>

                                <!-- Fecha -->
                                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                                    <label for="requested_date" class="block text-sm font-medium text-slate-700">Fecha Solicitada</label>
                                    <input 
                                        type="date"
                                        id="requested_date"
                                        v-model="form.requested_date" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" 
                                    />
                                    <div v-if="form.errors.requested_date" class="text-red-500 text-xs mt-1">{{ form.errors.requested_date }}</div>
                                </div>

                                <!-- Hora de Inicio -->
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-slate-700">Hora de Inicio</label>
                                    <input 
                                        type="time"
                                        id="start_time"
                                        v-model="form.start_time" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" 
                                    />
                                    <div v-if="form.errors.start_time" class="text-red-500 text-xs mt-1">{{ form.errors.start_time }}</div>
                                </div>

                                <!-- Hora de Finalización -->
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-slate-700">Hora de Finalización</label>
                                    <input 
                                        type="time"
                                        id="end_time"
                                        v-model="form.end_time" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" 
                                    />
                                    <div v-if="form.errors.end_time" class="text-red-500 text-xs mt-1">{{ form.errors.end_time }}</div>
                                </div>

                                <!-- Magnitud (Scale) -->
                                <div>
                                    <label for="scale" class="block text-sm font-medium text-slate-700">Magnitud del Trasteo</label>
                                    <select id="scale" v-model="form.scale" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                        <option value="" disabled>Seleccione la magnitud</option>
                                        <option value="light">Pequeña/Cajas</option>
                                        <option value="medium">Mediana/Mobiliario</option>
                                        <option value="heavy">Grande/Trasteo Completo</option>
                                    </select>
                                    <div v-if="form.errors.scale" class="text-red-500 text-xs mt-1">{{ form.errors.scale }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 transition-colors">
                                    Solicitar Autorización
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- History Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-slate-900">Historial de Solicitudes</h3>
                        </div>

                        <div v-if="moveRequests.length === 0" class="text-center py-12 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                            <p class="text-sm text-slate-500">No tiene solicitudes de mudanza registradas.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unidad</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha / Horario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Magnitud</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <tr v-for="request in moveRequests" :key="request.id" class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                            {{ request.unit?.identifier }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ getTypeText(request.type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ formatDate(request.requested_date) }} <br>
                                            <span class="text-xs text-slate-400">{{ formatTime(request.start_time) }} - {{ formatTime(request.end_time) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ getScaleText(request.scale) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-normal text-sm">
                                            <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full border', getStatusColor(request.status)]">
                                                {{ getStatusText(request.status) }}
                                            </span>
                                            <div v-if="request.admin_notes" class="mt-2 text-sm text-gray-500">
                                                {{ request.admin_notes }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
