<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';

const { data, isLoading, error } = useApiData('/api/cockpit/work-queue');
</script>

<template>
    <Head title="Cola de Trabajo" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">Cola de Trabajo (Guardas y Admins)</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div v-if="isLoading" class="text-gray-500 bg-white p-6 shadow-sm sm:rounded-lg">
                    Cargando información...
                </div>
                
                <div v-else-if="error" class="text-red-500 bg-white p-6 shadow-sm sm:rounded-lg">
                    {{ error }}
                </div>
                
                <template v-else-if="data && data.tasks">
                    <div class="mb-4 text-sm text-gray-500 bg-white p-4 shadow-sm sm:rounded-lg">
                        Rol activo: <span class="font-semibold text-gray-700 capitalize">{{ data.role }}</span> | 
                        Generado: <span class="font-mono text-gray-600">{{ new Date(data.generated_at).toLocaleString() }}</span>
                    </div>

                    <div v-for="(items, category) in data.tasks" :key="category" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <h3 class="text-lg font-medium capitalize text-indigo-700 mb-4">{{ String(category).replace(/_/g, ' ') }}</h3>
                            
                            <div v-if="!items || items.length === 0" class="text-gray-500 text-sm">
                                No hay tareas pendientes en esta categoría.
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div v-for="item in items" :key="item.id || item.reference || Math.random()" class="bg-gray-50 border border-gray-100 rounded p-4 text-sm">
                                    <pre class="whitespace-pre-wrap text-xs text-gray-700 font-sans">{{ item }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
