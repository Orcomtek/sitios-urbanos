<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { usePage, Link } from '@inertiajs/vue3';

const props = defineProps<{
    isOpen: boolean;
    unitId: number | null;
    isLoading: boolean;
}>();

const emit = defineEmits(['close']);

// The hydrated unit with its residents
const unitData = ref<any>(null);

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;

const getTaxonomyLabel = (type: string, value: string) => {
    if (type === 'resident_type') {
        if (value === 'family_owner') return 'Familiar (Propietario)';
        if (value === 'family_tenant') return 'Familiar (Inquilino)';
    }
    const taxonomies = (page.props.taxonomies as any)?.[type] || [];
    const item = taxonomies.find((t: any) => t.value === value);
    return item ? item.label : value;
};

watch(() => props.isOpen, async (newVal) => {
    if (newVal && props.unitId) {
        try {
            // Fetch the eager-loaded unit data using a standard Inertia JSON fetch or raw Axios.
            // Using raw axios for this slide-over structural demo based on the controller `show` JSON response:
            const response = await axios.get(route('tenant.admin.core.units.show', { community_slug: communitySlug, unit: props.unitId }));
            unitData.value = response.data.unit;
        } catch (e) {
            console.error(e);
        }
    } else {
        unitData.value = null; // Clear on close
    }
});

const closePanel = () => {
    emit('close');
};
</script>

<template>
    <div
        v-show="isOpen"
        class="fixed inset-0 overflow-hidden z-50 bg-gray-500 bg-opacity-75 transition-opacity"
        aria-labelledby="slide-over-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div
                class="w-screen max-w-md transform transition ease-in-out duration-500 sm:duration-700 h-full flex flex-col bg-white shadow-xl overflow-y-auto relative"
            >
                <div class="px-4 py-6 sm:px-6 bg-indigo-700 text-white flex justify-between items-center">
                    <h2 class="text-lg font-medium" id="slide-over-title">Detalles de Unidad</h2>
                    <button @click="closePanel" class="text-white hover:text-gray-200">
                        <span class="sr-only">Cerrar panel</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 flex-1">
                    <div v-if="isLoading && !unitData" class="flex justify-center p-12">
                        <!-- Skeleton or Spinner -->
                        Cargando información...
                    </div>
                    
                    <div v-else-if="unitData" class="space-y-6">
                        <!-- Unit Details -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Identificador</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ unitData.identifier }} ({{ getTaxonomyLabel('property_type', unitData.property_type) }})</p>
                        </div>
                        
                        <!-- Residents Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Residentes</h3>
                                <Link :href="route('tenant.admin.core.residents.create', { community_slug: communitySlug, unit_id: unitData.id })" class="text-sm text-indigo-600 font-medium hover:text-indigo-900">
                                    + Añadir
                                </Link>
                            </div>
                            
                            <ul role="list" class="divide-y divide-gray-200" v-if="unitData.residents && unitData.residents.length > 0">
                                <li v-for="resident in unitData.residents" :key="resident.id" class="py-4 flex justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ resident.full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ getTaxonomyLabel('resident_type', resident.computed_role || resident.resident_type) }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Activo
                                        </span>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-sm text-gray-500 py-4 italic">
                                No hay residentes registrados.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
