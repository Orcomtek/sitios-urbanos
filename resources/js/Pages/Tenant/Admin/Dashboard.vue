<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const tenant = (page.props.tenant as any);
const is_admin = (page.props as any).is_admin;
const matrix_status = (page.props as any).matrix_status;
</script>

<template>
    <Head title="Panel de Administración" />

    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Warning Banner for Coefficient Inconsistencies -->
                <div v-if="is_admin && matrix_status?.has_warnings" class="mb-8 bg-amber-50 border-l-4 border-amber-400 p-4 shadow-sm sm:rounded-r-xl">
                    <p class="text-sm text-amber-800">
                        ⚠️ Advertencia Financiera: La matriz de coeficientes de la copropiedad presenta inconsistencias (Suma total: {{ matrix_status.total_coefficient }}). Verifique que todas las unidades tengan asignado su coeficiente correcto para evitar errores en la facturación automática.
                    </p>
                </div>

                <!-- Quiet Luxury Welcome Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="px-8 py-10">
                        <h2 class="text-3xl font-light tracking-tight text-gray-900 mb-2">
                            Resumen de la Comunidad
                        </h2>
                        <p class="text-gray-500 text-lg mb-8">
                            Bienvenido al panel de administración de <span class="font-medium text-gray-800">{{ tenant?.community?.name || 'la comunidad' }}</span>.
                        </p>
                        
                        <div class="bg-gray-50/50 rounded-lg p-6 border border-gray-50">
                            <div class="flex items-center text-gray-500 italic">
                                <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Los indicadores analíticos están en desarrollo y se integrarán próximamente.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
