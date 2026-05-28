<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';

const { data, isLoading, error } = useApiData('/api/cockpit/dashboard');
</script>

<template>
    <Head title="Panel de Control (Cockpit)" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">Panel Operativo</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen</h3>
                        
                        <div v-if="isLoading" class="text-gray-500">
                            Cargando datos del panel...
                        </div>
                        
                        <div v-else-if="error" class="text-red-500">
                            {{ error }}
                        </div>
                        
                        <div v-else-if="data">
                            <div class="mb-4 text-sm text-gray-500">
                                Rol activo: <span class="font-semibold text-gray-700 capitalize">{{ data.role }}</span> | 
                                Generado: <span class="font-mono text-gray-600">{{ new Date(data.generated_at).toLocaleString() }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Metrics based on DashboardResource output -->
                                <div v-for="(value, key) in data.widgets" :key="key" class="bg-gray-50 p-4 border rounded-md">
                                    <dt class="text-sm font-medium text-gray-500 truncate capitalize">{{ String(key).replace(/_/g, ' ') }}</dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                        <template v-if="typeof value === 'object' && value !== null">
                                            <!-- For nested objects like active_emergencies -->
                                            <div class="text-sm font-normal text-gray-600 mt-2">
                                                <div v-for="(subVal, subKey) in value" :key="subKey">
                                                    <span class="capitalize">{{ String(subKey).replace(/_/g, ' ') }}:</span> {{ subVal }}
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            {{ value }}
                                        </template>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
