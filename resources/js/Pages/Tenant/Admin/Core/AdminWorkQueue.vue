<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import axios from 'axios';

const { data, isLoading, error, refetch } = useApiData('/api/cockpit/admin-work-queue');
const page = usePage();
const communityId = (page.props.tenant as any)?.community?.id;

// Translation Dictionary for Categories
const TASK_LABELS: Record<string, string> = {
    'listing_active': 'Anuncios por moderar',
    'pqrs_open': 'PQRS pendientes',
    'poll_active': 'Encuestas activas',
    'invoice_pending': 'Facturas por revisar',
    'announcement_active': 'Anuncios activos'
};

const translateCategory = (key: string | number) => {
    const stringKey = String(key);
    return TASK_LABELS[stringKey] || stringKey.replace(/_/g, ' ');
};

// Safe Task Grouping computing optionally chained values
const groupedTasks = computed(() => {
    if (!data.value?.tasks) return {};
    
    // Fallback protection in case Laravel API returned an object of tasks instead of a flat array
    const tasksArray = Array.isArray(data.value.tasks) 
        ? data.value.tasks 
        : Object.values(data.value.tasks);
        
    const groups: Record<string, any[]> = {};
    
    tasksArray.forEach((task: any) => {
        if (!groups[task.type]) {
            groups[task.type] = [];
        }
        groups[task.type].push(task);
    });
    
    return groups;
});

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

const isModerating = ref<Record<string | number, boolean>>({});

const moderateListing = async (id: number) => {
    isModerating.value[id] = true;
    try {
        await axios.patch(`/api/ecosystem/listings/${id}/moderate`, { status: 'removed' });
        await refetch();
    } catch (e: any) {
        console.error(e);
        alert(e.response?.data?.message || 'Hubo un error al moderar el anuncio.');
    } finally {
        isModerating.value[id] = false;
    }
};
</script>

<template>
    <Head title="Cola Operativa (Administración)" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">
                Cola Operativa (Administración)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Loading State -->
                <div v-if="isLoading" class="p-6 text-gray-500 bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Cargando información administrativa...</span>
                    </div>
                </div>
                
                <!-- Error State -->
                <div v-else-if="error" class="p-6 text-red-600 bg-red-50 border-l-4 border-red-500 shadow-sm sm:rounded-lg">
                    <p class="font-medium">{{ error }}</p>
                </div>
                
                <!-- Data Display -->
                <template v-else-if="data">
                    <div class="mb-6 p-4 text-sm text-gray-500 bg-white shadow-sm sm:rounded-lg border border-gray-100 flex items-center justify-between">
                        <div>
                            Rol activo: <span class="font-semibold text-gray-700 capitalize">{{ data.role }}</span>
                        </div>
                        <div class="text-xs">
                            Actualizado: <span class="font-mono text-gray-600">{{ new Date(data.generated_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Bulletproof Empty State -->
                    <div v-if="!data?.tasks || Object.keys(groupedTasks).length === 0" class="p-8 text-center bg-white shadow-sm sm:rounded-lg border border-gray-200">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">Mesa limpia</h3>
                        <p class="mt-1 text-sm text-gray-500">No hay tareas administrativas pendientes en este momento.</p>
                    </div>
                    
                    <!-- Grouped Tasks Rendering -->
                    <template v-else>
                        <div v-for="(tasks, category) in groupedTasks" :key="category" class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="p-6 text-gray-900">
                                
                                <h3 class="text-lg font-bold text-emerald-700 mb-4 capitalize">
                                    {{ translateCategory(category) }}
                                </h3>
                                
                                <div class="space-y-4">
                                    <div v-for="task in tasks" :key="task.id" class="p-5 mb-4 bg-white border rounded-lg shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors hover:border-emerald-200">
                                        
                                        <!-- Left Side: Informational Context -->
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ task.label }}</div>
                                            <p class="text-sm text-gray-500 mt-1 flex items-center" v-if="task.unit">
                                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                                Unidad: <span class="font-medium text-gray-700 ml-1">{{ task.unit.unit_number || task.unit.identifier || task.unit.name }}</span>
                                            </p>
                                        </div>
                                        
                                        <!-- Right Side: Interaction Action -->
                                        <div class="shrink-0 text-right">
                                            <button 
                                                v-if="task.action === 'moderate'"
                                                @click="moderateListing(task.id)" 
                                                :disabled="isModerating[task.id]"
                                                class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 transition-colors inline-flex items-center"
                                            >
                                                <svg v-if="isModerating[task.id]" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ isModerating[task.id] ? 'Procesando...' : 'Ocultar anuncio' }}
                                            </button>
                                            
                                            <!-- Standard Action Badges (Fallback) -->
                                            <span v-else-if="task.action" class="px-3 py-1.5 bg-gray-50 text-gray-600 rounded-md text-xs font-semibold uppercase tracking-wider border border-gray-200">
                                                {{ task.action }}
                                            </span>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </AppLayout>
</template>