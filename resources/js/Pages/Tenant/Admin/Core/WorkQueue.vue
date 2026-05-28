<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const { data, isLoading, error, refetch } = useApiData('/api/cockpit/work-queue');
const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;
const communityId = (page.props.tenant as any)?.community?.id;

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const debouncedRefetch = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        refetch();
    }, 300);
};

onMounted(() => {
    // @ts-ignore
    if (communityId && window.Echo) {
        // @ts-ignore
        window.Echo.private(`community.${communityId}`)
            .listen('.TenantActivityUpdated', (e: any) => {
                debouncedRefetch();
            });
    }
});

onUnmounted(() => {
    // @ts-ignore
    if (communityId && window.Echo) {
        // @ts-ignore
        window.Echo.leave(`community.${communityId}`);
    }
    if (debounceTimer) clearTimeout(debounceTimer);
});

const executingItems = ref(new Set<number>());
const actionError = ref<string | null>(null);

const executeAction = async (item: any) => {
    executingItems.value.add(item.id);
    actionError.value = null;

    try {
        let endpoint = '';
        if (item.type === 'visitor_pending' && item.action === 'enter') {
            endpoint = `/api/security/visitors/${item.id}/enter`;
        } else if (item.type === 'package_received' && item.action === 'deliver') {
            endpoint = `/api/security/packages/${item.id}/deliver`;
        } else if (item.type === 'invitation_active' && item.action === 'consume') {
            endpoint = `/api/security/invitations/${item.id}/consume`;
        } else if (item.type === 'emergency_active' && item.action === 'acknowledge') {
            endpoint = `/api/security/emergencies/${item.id}/ack`;
        } else {
            throw new Error('Tipo de acción no soportada');
        }

        await axios.patch(endpoint);
        await refetch();
    } catch (err: any) {
        actionError.value = err.response?.data?.message || err.message || 'Error al ejecutar la acción';
    } finally {
        executingItems.value.delete(item.id);
    }
};

const getButtonText = (type: string) => {
    switch(type) {
        case 'visitor_pending': return 'Registrar entrada';
        case 'package_received': return 'Marcar entregado';
        case 'invitation_active': return 'Consumir acceso';
        case 'emergency_active': return 'Atender';
        default: return 'Ejecutar';
    }
};

const getButtonClass = (type: string) => {
    switch(type) {
        case 'emergency_active': return 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white';
        case 'visitor_pending': return 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 text-white';
        case 'package_received': return 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500 text-white';
        default: return 'bg-gray-800 hover:bg-gray-900 focus:ring-gray-900 text-white';
    }
};
</script>

<template>
    <Head title="Cola de Trabajo" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-4 px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 mb-4 sm:mb-0">Cola de Trabajo (Operadores)</h2>
                <button 
                    @click="refetch" 
                    :disabled="isLoading"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    Actualizar
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Errores de acciones -->
                <div v-if="actionError" class="text-red-700 bg-red-100 border border-red-300 p-4 shadow-sm sm:rounded-lg flex justify-between items-center transition-all">
                    <span>{{ actionError }}</span>
                    <button @click="actionError = null" class="text-red-500 hover:text-red-700 font-bold ml-4">&times;</button>
                </div>

                <div v-if="isLoading && (!data || !data.tasks)" class="text-gray-500 bg-white p-6 shadow-sm sm:rounded-lg flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Cargando información...
                </div>
                
                <div v-else-if="error" class="text-red-500 bg-white p-6 shadow-sm sm:rounded-lg">
                    {{ error }}
                </div>
                
                <template v-else-if="data && data.tasks">
                    <div class="mb-4 text-sm text-gray-500 bg-white p-4 shadow-sm sm:rounded-lg flex justify-between items-center border border-gray-100">
                        <div>
                            Rol activo: <span class="font-semibold text-gray-700 capitalize">{{ data.role }}</span>
                        </div>
                        <div class="text-xs">
                            Actualizado: <span class="font-mono text-gray-600">{{ new Date(data.generated_at).toLocaleTimeString() }}</span>
                        </div>
                    </div>

                    <div v-if="!data.tasks || data.tasks.length === 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100 p-10 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-900">No hay tareas pendientes</p>
                            <p class="mt-1 text-sm text-gray-500">La cola de trabajo está vacía en este momento.</p>
                        </div>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="item in data.tasks" :key="item.id + '-' + item.type" class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden flex flex-col sm:flex-row sm:items-center justify-between p-4 hover:shadow-md transition">
                            <div class="mb-4 sm:mb-0 flex-grow pr-4">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide"
                                        :class="{
                                            'bg-red-100 text-red-800': item.type === 'emergency_active',
                                            'bg-indigo-100 text-indigo-800': item.type === 'visitor_pending',
                                            'bg-emerald-100 text-emerald-800': item.type === 'package_received',
                                            'bg-amber-100 text-amber-800': item.type === 'invitation_active'
                                        }"
                                    >
                                        {{ item.type.split('_')[0] }}
                                    </span>
                                    <span v-if="item.unit" class="text-sm font-bold text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded">
                                        Und: {{ item.unit.unit_number }}
                                    </span>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 leading-snug">{{ item.label }}</h3>
                            </div>
                            
                            <div class="flex items-center space-x-3 sm:ml-4 shrink-0">
                                <button
                                    @click="executeAction(item)"
                                    :disabled="executingItems.has(item.id)"
                                    class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto min-w-[160px]"
                                    :class="getButtonClass(item.type)"
                                >
                                    <svg v-if="executingItems.has(item.id)" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ getButtonText(item.type) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
