<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useApiData } from '@/lib/useApiData';
import { onMounted, onUnmounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const { data, isLoading, error, refetch } = useApiData('/api/cockpit/admin-work-queue');
const page = usePage();
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

const isModerating = ref<Record<number, boolean>>({});

const moderateListing = async (id: number) => {
    isModerating.value[id] = true;
    try {
        await axios.patch(`/api/ecosystem/listings/${id}/moderate`, { status: 'removed' });
        await refetch();
    } catch (e) {
        console.error(e);
        alert('Hubo un error al moderar el anuncio.');
    } finally {
        isModerating.value[id] = false;
    }
};
</script>

<template>
    <Head title="Cola Operativa (Administración)" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">Cola Operativa (Administración)</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div v-if="isLoading" class="text-gray-500 bg-white p-6 shadow-sm sm:rounded-lg">
                    Cargando información administrativa...
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
                            <h3 class="text-lg font-medium capitalize text-emerald-700 mb-4">{{ String(category).replace(/_/g, ' ') }}</h3>
                            
                            <div v-if="!items || items.length === 0" class="text-gray-500 text-sm">
                                No hay tareas administrativas pendientes en esta categoría.
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div v-for="item in items" :key="item.id || item.reference || Math.random()" class="bg-gray-50 border border-gray-100 rounded p-4 text-sm">
                                    <pre class="whitespace-pre-wrap text-xs text-gray-700 font-sans mb-3">{{ item }}</pre>
                                    
                                    <div v-if="item.type === 'listing_active' && item.action === 'moderate'" class="mt-2 text-right">
                                        <button 
                                            @click="moderateListing(item.id)" 
                                            :disabled="isModerating[item.id]"
                                            class="px-3 py-1 bg-red-600 text-white rounded shadow-sm hover:bg-red-700 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 transition-colors"
                                        >
                                            {{ isModerating[item.id] ? 'Procesando...' : 'Ocultar anuncio' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
