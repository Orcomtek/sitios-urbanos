<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { computed } from 'vue';
import { TruckIcon, HeartIcon, UserGroupIcon } from '@heroicons/vue/20/solid';

const props = defineProps<{
    show: boolean;
    unit: any;
}>();

const emit = defineEmits(['close']);

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const getTaxonomyLabel = (type: string, value: string) => {
    if (type === 'resident_type') {
        if (value === 'family_owner') return 'Familiar (Propietario)';
        if (value === 'family_tenant') return 'Familiar (Inquilino)';
    }
    const taxonomies = (page.props.taxonomies as any)?.[type] || [];
    const item = taxonomies.find((t: any) => t.value === value);
    return item ? item.label : value;
};

const translateFamily = (relationship: string) => {
    const translations: Record<string, string> = {
        spouse: 'Esposo/a',
        child: 'Hijo/a',
        parent: 'Padre/Madre',
        sibling: 'Hermano/a',
        other: 'Otro'
    };
    return translations[relationship] || relationship;
};

const translatePet = (species: string) => {
    const translations: Record<string, string> = {
        dog: 'Perro',
        cat: 'Gato',
        bird: 'Ave',
        other: 'Otro'
    };
    return translations[species] || species;
};

const translateVehicle = (type: string) => {
    const translations: Record<string, string> = {
        car: 'Carro',
        motorcycle: 'Moto',
        bicycle: 'Bicicleta',
        other: 'Otro'
    };
    return translations[type] || type;
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-indigo-700 text-white rounded-t-lg">
            <h2 class="text-lg font-medium" id="modal-title">Detalles de Unidad</h2>
            <button @click="close" class="text-white hover:text-gray-200">
                <span class="sr-only">Cerrar panel</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div v-if="unit" class="space-y-6">
                <!-- Unit Details -->
                <!-- Unit Details & Attributes -->
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Identificador</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ unit.identifier }}
                            <span class="font-normal text-gray-500">({{ getTaxonomyLabel('property_type', unit.property_type || unit.type) }})</span>
                            <span v-if="unit.is_rented" class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Arrendada</span>
                        </p>
                    </div>
                    <div class="border-t sm:border-t-0 sm:border-l border-gray-200 pt-4 sm:pt-0 sm:pl-4">
                        <h3 class="text-sm font-medium text-gray-500 mb-1.5">Atributos de Unidad</h3>
                        <div class="flex gap-2">
                            <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200 shadow-sm">Parqueadero: N/A</span>
                            <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200 shadow-sm">Depósito: N/A</span>
                        </div>
                    </div>
                </div>
                
                <!-- Residents Section (Bento style grid/list) -->
                <div class="pt-2">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Residentes</h3>
                        <Link :href="route('tenant.admin.core.residents.create', { community_slug: communitySlug, unit_id: unit.id })" class="text-sm text-indigo-600 font-medium hover:text-indigo-900">
                            + Añadir
                        </Link>
                    </div>
                    
                    <ul role="list" class="flex flex-col space-y-4" v-if="unit.residents && unit.residents.length > 0">
                        <li v-for="resident in unit.residents" :key="resident.id" class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200 divide-y divide-gray-200">
                            <div class="flex w-full items-center justify-between space-x-6 p-4">
                                <div class="flex-1 truncate">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="truncate text-sm font-medium text-gray-900">{{ resident.full_name }}</h3>
                                        <span class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activo</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-gray-500">{{ getTaxonomyLabel('resident_type', resident.computed_role || resident.resident_type) }}</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 border-t border-gray-100" v-if="(resident.vehicles && resident.vehicles.length) || (resident.pets && resident.pets.length) || (resident.family_members && resident.family_members.length)">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-xs text-gray-600">
                                    <!-- Family -->
                                    <div v-for="family in resident.family_members" :key="'fam-'+family.id" class="flex items-center gap-2 border-l-2 border-gray-200 pl-2">
                                        <UserGroupIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                        <span><span class="font-medium text-gray-900">{{ family.name }}</span> - {{ translateFamily(family.relationship) }}</span>
                                    </div>
                                    <!-- Vehicles -->
                                    <div v-for="vehicle in resident.vehicles" :key="'veh-'+vehicle.id" class="flex items-center gap-2 border-l-2 border-gray-200 pl-2">
                                        <TruckIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                        <span><span class="font-medium text-gray-900">{{ vehicle.license_plate }}</span> - {{ translateVehicle(vehicle.type) }}, {{ vehicle.brand }}, {{ vehicle.color }}</span>
                                    </div>
                                    <!-- Pets -->
                                    <div v-for="pet in resident.pets" :key="'pet-'+pet.id" class="flex items-center gap-2 border-l-2 border-gray-200 pl-2">
                                        <HeartIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                        <span><span class="font-medium text-gray-900">{{ pet.name }}</span> - {{ translatePet(pet.species) }} <span v-if="pet.breed">({{ pet.breed }})</span></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="text-sm text-gray-500 py-4 italic text-center bg-gray-50 rounded-lg border border-gray-100">
                        No hay residentes registrados.
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
