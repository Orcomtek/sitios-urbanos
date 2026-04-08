<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';

const { data, isLoading, error } = useApiData('/api/cockpit/activity');

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;
const tenantRole = (page.props.tenant as any)?.role;

const getSourceColor = (source: string) => {
    return source === 'security_log' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
};

const getSourceLabel = (source: string) => {
    return source === 'security_log' ? 'Bitácora' : 'Notificación';
};
</script>

<template>
    <Head title="Registro de Actividad" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">Registro de Actividad</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Historial Reciente</h3>
                            <div class="text-sm text-gray-500">
                                Mostrando últimos 20 eventos
                            </div>
                        </div>

                        <div v-if="isLoading" class="text-gray-500 py-4">
                            Cargando actividad...
                        </div>
                        
                        <div v-else-if="error" class="text-red-500 py-4">
                            {{ error }}
                        </div>
                        
                        <div v-else-if="data && data.length > 0">
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    <li v-for="(item, itemIdx) in data" :key="item.id">
                                        <div class="relative pb-8">
                                            <span v-if="itemIdx !== data.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white bg-gray-100">
                                                        <svg v-if="item.source === 'security_log'" class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                                        </svg>
                                                        <svg v-else class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-gray-900 font-medium whitespace-pre-wrap">{{ item.title }}</p>
                                                        <p class="text-sm text-gray-500 mt-0.5 whitespace-pre-wrap">{{ item.message }}</p>
                                                        <div class="mt-2 flex items-center gap-x-2">
                                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ring-gray-500/10" :class="getSourceColor(item.source)">
                                                                {{ getSourceLabel(item.source) }}
                                                            </span>
                                                            <span class="text-xs text-gray-400 capitalize">{{ String(item.type).replace(/_/g, ' ') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500 flex flex-col items-end">
                                                        <span>{{ new Date(item.created_at).toLocaleDateString('es-CO', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div v-else class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay actividad reciente</h3>
                            <p class="mt-1 text-sm text-gray-500">Los eventos importantes aparecerán aquí.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
