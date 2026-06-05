<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { MagnifyingGlassIcon, HomeIcon, UserIcon, UsersIcon, ShieldCheckIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const searchQuery = ref('');
const isSearching = ref(false);
const results = ref<any>({
    units: [],
    vehicles: [],
    pets: [],
    family_members: [],
    residents: [],
});

let searchTimeout: any = null;

const performSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    
    if (!searchQuery.value.trim()) {
        results.value = { units: [], vehicles: [], pets: [], family_members: [], residents: [] };
        return;
    }

    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            const response = await axios.get(route('tenant.admin.security.radar.search', { community_slug: communitySlug.value }), {
                params: { query: searchQuery.value }
            });
            results.value = response.data;
        } catch (error) {
            console.error('Error searching radar:', error);
        } finally {
            isSearching.value = false;
        }
    }, 300); // Debounce 300ms
};

const hasResults = computed(() => {
    return results.value.units.length > 0 || 
           results.value.vehicles.length > 0 || 
           results.value.pets.length > 0 || 
           results.value.family_members.length > 0 || 
           results.value.residents.length > 0;
});
const translateFamily = (val: string) => {
    const map: Record<string, string> = {
        spouse: 'Esposo/a',
        child: 'Hijo/a',
        parent: 'Padre/Madre',
        other: 'Otro'
    };
    return map[val] || val;
};

const translatePet = (val: string) => {
    const map: Record<string, string> = {
        dog: 'Perro',
        cat: 'Gato',
        other: 'Otro'
    };
    return map[val] || val;
};

const translateVehicle = (val: string) => {
    const map: Record<string, string> = {
        car: 'Carro',
        motorcycle: 'Moto',
        bicycle: 'Bicicleta',
        other: 'Otro'
    };
    return map[val] || val;
};

const getRoleLabel = (role: string) => {
    if (role === 'owner' || role === 'propietario') return 'Propietario';
    if (role === 'tenant' || role === 'inquilino') return 'Inquilino';
    if (role === 'family') return 'Familiar';
    if (role === 'family_owner') return 'Familiar (Propietario)';
    if (role === 'family_tenant') return 'Familiar (Inquilino)';
    if (role === 'dependent') return 'Familiar / Dependiente';
    return role;
};
</script>

<template>
    <Head title="Radar de Seguridad" />

    <AppLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                    <ShieldCheckIcon class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Radar de Seguridad</h2>
                    <p class="text-sm text-gray-500">Búsqueda rápida de unidades, residentes, vehículos y mascotas.</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Massive Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                    </div>
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        @input="performSearch"
                        class="block w-full pl-14 pr-3 py-6 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-2xl shadow-sm transition-shadow hover:shadow-md" 
                        placeholder="Buscar placa, nombre, unidad..." 
                    />
                    <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="animate-spin h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Bento Grid Results -->
                <div v-if="hasResults" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Unidades -->
                    <div v-if="results.units.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden col-span-1 lg:col-span-2">
                        <div class="px-4 py-3 bg-indigo-50/50 border-b border-gray-100 flex items-center gap-2">
                            <HomeIcon class="w-5 h-5 text-emerald-600" />
                            <h3 class="font-semibold text-indigo-900">Unidades</h3>
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="unit in results.units" :key="unit.id" class="p-4 border border-gray-100 rounded-lg bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="text-lg font-bold text-gray-900 mb-2">{{ unit.identifier }}</div>
                                <div v-if="unit.residents?.length > 0">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Residentes Activos</div>
                                    <ul class="space-y-1">
                                        <li v-for="resident in unit.residents" :key="resident.id" class="text-sm text-gray-700 flex items-center gap-1">
                                            <UserIcon class="w-3 h-3 text-gray-400" /> {{ resident.full_name }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Residentes -->
                    <div v-if="results.residents.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-blue-50/50 border-b border-gray-100 flex items-center gap-2">
                            <UserIcon class="w-5 h-5 text-blue-600" />
                            <h3 class="font-semibold text-blue-900">Residentes Titulares</h3>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="res in results.residents" :key="res.id" class="p-4 hover:bg-gray-50 transition">
                                <div class="font-medium text-gray-900">{{ res.full_name }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-[10px]" v-if="res.computed_role">{{ getRoleLabel(res.computed_role) }}</span>
                                    <span class="text-gray-400" v-if="res.computed_role">•</span>
                                    <HomeIcon class="w-3 h-3" /> {{ res.unit?.identifier || 'Sin unidad' }}
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Familiares -->
                    <div v-if="results.family_members.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-green-50/50 border-b border-gray-100 flex items-center gap-2">
                            <UsersIcon class="w-5 h-5 text-green-600" />
                            <h3 class="font-semibold text-green-900">Familiares</h3>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="fam in results.family_members" :key="fam.id" class="p-4 hover:bg-gray-50 transition">
                                <div class="font-medium text-gray-900">{{ fam.name }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-[10px]">{{ translateFamily(fam.relationship) }}</span>
                                    <span class="text-gray-400">•</span>
                                    <HomeIcon class="w-3 h-3" /> {{ fam.resident?.unit?.identifier || 'Desconocido' }}
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Vehículos -->
                    <div v-if="results.vehicles.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-orange-50/50 border-b border-gray-100 flex items-center gap-2">
                            <!-- Using a simple icon for vehicle as heroicons doesn't have a specific car icon -->
                            <ShieldCheckIcon class="w-5 h-5 text-orange-600" />
                            <h3 class="font-semibold text-orange-900">Vehículos</h3>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="veh in results.vehicles" :key="veh.id" class="p-4 hover:bg-gray-50 transition flex items-center justify-between">
                                <div>
                                    <div class="font-mono font-bold text-gray-900 uppercase tracking-widest text-lg">{{ veh.license_plate }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ translateVehicle(veh.type) }} • {{ veh.brand }} • {{ veh.color }}</div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    <div class="flex items-center gap-1 justify-end"><HomeIcon class="w-3 h-3" /> {{ veh.resident?.unit?.identifier || 'N/A' }}</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Mascotas -->
                    <div v-if="results.pets.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-purple-50/50 border-b border-gray-100 flex items-center gap-2">
                            <HandThumbUpIcon class="w-5 h-5 text-purple-600" />
                            <h3 class="font-semibold text-purple-900">Mascotas</h3>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="pet in results.pets" :key="pet.id" class="p-4 hover:bg-gray-50 transition">
                                <div class="font-medium text-gray-900">{{ pet.name }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    {{ translatePet(pet.species) }} • {{ pet.breed }}
                                    <span class="text-gray-400 mx-1">•</span>
                                    <HomeIcon class="w-3 h-3" /> {{ pet.resident?.unit?.identifier || 'N/A' }}
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Empty State -->
                <div v-if="!hasResults && searchQuery.length > 0 && !isSearching" class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                    <ShieldCheckIcon class="mx-auto h-12 w-12 text-gray-300" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin resultados</h3>
                    <p class="mt-1 text-sm text-gray-500">No se encontraron registros que coincidan con "{{ searchQuery }}".</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
