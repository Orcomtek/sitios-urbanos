<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const data = ref([]);
const loading = ref(true);
const error = ref(null);
const expanded = ref({});
const isCreating = ref(false);

const form = useForm({
    type: 'petition',
    subject: '',
    description: '',
    is_anonymous: false
});

const formSuccess = ref('');
const formError = ref('');

const toggleExpand = (id) => {
    expanded.value[id] = !expanded.value[id];
};

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/governance/pqrs');
        data.value = response.data.data || [];
        error.value = null;
    } catch (e) {
        console.error('Error fetching PQRS:', e);
        error.value = 'Error al cargar las solicitudes. Por favor intente más tarde.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

const submitPqrs = async () => {
    formError.value = '';
    formSuccess.value = '';
    
    try {
        await axios.post('/api/governance/pqrs', form);
        formSuccess.value = 'Solicitud creada con éxito.';
        form.reset();
        isCreating.value = false;
        await fetchData();
        setTimeout(() => formSuccess.value = '', 4000);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            formError.value = 'Por favor verifica la información ingresada.';
        } else {
            formError.value = e.response?.data?.message || 'Hubo un error al crear la solicitud.';
        }
    }
};

const formatDate = (dateString) => {
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
    <AppLayout title="Mis PQRS">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mis Requests (PQRS)
                </h2>
                <Link :href="route('tenant.cockpit.resident')" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    &larr; Volver a Cabina
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Success Message -->
                <div v-if="formSuccess" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm transition-all">
                    <p class="text-green-700 text-sm font-medium">{{ formSuccess }}</p>
                </div>

                <!-- Create Form Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center cursor-pointer" @click="isCreating = !isCreating">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Radicar Nueva PQRS</h3>
                            <p class="text-xs text-gray-500 mt-1">Envía una nueva petición, queja, reclamo o sugerencia a la administración.</p>
                        </div>
                        <button class="text-indigo-600 hover:text-indigo-800 font-medium text-sm focus:outline-none">
                            {{ isCreating ? 'Cancelar' : 'Crear Nueva' }}
                        </button>
                    </div>

                    <div v-show="isCreating" class="p-6 transition-all duration-300">
                        <form @submit.prevent="submitPqrs" class="space-y-4">
                            <div v-if="formError" class="bg-red-50 text-red-700 p-3 rounded-md text-sm mb-4 border border-red-100">
                                {{ formError }}
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Solicitud</label>
                                    <select v-model="form.type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="petition">Petición</option>
                                        <option value="complaint">Queja</option>
                                        <option value="claim">Reclamo</option>
                                        <option value="suggestion">Sugerencia</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                                    <input type="text" v-model="form.subject" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Resumen corto de tu solicitud" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <textarea v-model="form.description" required rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Detalla tu petición, sugerencia, queja o reclamo..."></textarea>
                            </div>

                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="is_anonymous" v-model="form.is_anonymous" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_anonymous" class="font-medium text-gray-700">Enviar como Anónimo</label>
                                    <p class="text-gray-500">Tu nombre no será visible para la administración al radicar esta PQRS.</p>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Enviar Solicitud
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-semibold text-lg text-gray-800">Historial de Solicitudes</h3>
                        <button @click="fetchData" class="text-sm text-gray-600 hover:text-indigo-600 focus:outline-none flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Actualizar
                        </button>
                    </div>

                    <div v-if="loading" class="p-6 space-y-4">
                        <div class="animate-pulse bg-gray-200 h-16 w-full rounded-md" v-for="i in 3" :key="i" />
                    </div>

                    <div v-else-if="error" class="p-6 text-center text-red-600">
                        {{ error }}
                    </div>

                    <div v-else-if="data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay PQRS activas</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza radicando una nueva solicitud.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="pqrs in data" :key="pqrs.id" class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col sm:flex-row justify-between items-start cursor-pointer" @click="toggleExpand(pqrs.id)">
                                <div class="flex-1 pr-4">
                                    <div class="flex items-center space-x-3 mb-1">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 border border-gray-200">
                                            {{ pqrs.type_name }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border" :class="{
                                            'bg-yellow-100 text-yellow-800 border-yellow-200': pqrs.status === 'open',
                                            'bg-blue-100 text-blue-800 border-blue-200': pqrs.status === 'in_progress',
                                            'bg-green-100 text-green-800 border-green-200': pqrs.status === 'resolved',
                                            'bg-gray-100 text-gray-800 border-gray-200': pqrs.status === 'closed'
                                        }">
                                            {{ pqrs.status_name }}
                                        </span>
                                        <span v-if="pqrs.is_anonymous" class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 border border-purple-200">
                                            Modo Anónimo
                                        </span>
                                    </div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ pqrs.subject }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">Radicado el {{ formatDate(pqrs.created_at) }}</p>
                                </div>
                                <div class="mt-4 sm:mt-0 flex-shrink-0">
                                    <button class="text-sm font-medium text-indigo-600 focus:outline-none">
                                        {{ expanded[pqrs.id] ? 'Cerrar Detalles' : 'Ver Detalles' }}
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Inline Detail Expansion -->
                            <div v-show="expanded[pqrs.id]" class="mt-4 pt-4 border-t border-gray-100 space-y-4">
                                <div>
                                    <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Descripción</h5>
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ pqrs.description }}</p>
                                </div>
                                
                                <div v-if="pqrs.admin_response" class="bg-blue-50 border border-blue-100 p-4 rounded-md">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="text-xs font-medium text-blue-800 uppercase tracking-wider">Respuesta de la Administración</h5>
                                        <span v-if="pqrs.responded_at" class="text-xs text-blue-600">{{ formatDate(pqrs.responded_at) }}</span>
                                    </div>
                                    <p class="text-sm text-blue-900 whitespace-pre-line">{{ pqrs.admin_response }}</p>
                                </div>
                                <div v-else class="text-sm text-gray-500 italic border-l-2 border-gray-200 pl-3">
                                    Aún no hay respuesta de la administración para esta solicitud.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
