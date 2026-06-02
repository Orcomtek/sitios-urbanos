<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { PencilIcon, TrashIcon, UserGroupIcon, TruckIcon, HeartIcon } from '@heroicons/vue/24/outline';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import FamilyMemberFormModal from './Components/FamilyMemberFormModal.vue';
import VehicleFormModal from './Components/VehicleFormModal.vue';
import PetFormModal from './Components/PetFormModal.vue';

const props = defineProps<{
    familyMembers: Array<any>;
    vehicles: Array<any>;
    pets: Array<any>;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const activeTab = ref('family');

// Family Members
const isFamilyFormOpen = ref(false);
const editFamilyMember = ref<any>(null);
const openFamilyForm = (member: any = null) => {
    editFamilyMember.value = member;
    isFamilyFormOpen.value = true;
};
const closeFamilyForm = () => {
    isFamilyFormOpen.value = false;
    editFamilyMember.value = null;
};

// Vehicles
const isVehicleFormOpen = ref(false);
const editVehicle = ref<any>(null);
const openVehicleForm = (vehicle: any = null) => {
    editVehicle.value = vehicle;
    isVehicleFormOpen.value = true;
};
const closeVehicleForm = () => {
    isVehicleFormOpen.value = false;
    editVehicle.value = null;
};

// Pets
const isPetFormOpen = ref(false);
const editPet = ref<any>(null);
const openPetForm = (pet: any = null) => {
    editPet.value = pet;
    isPetFormOpen.value = true;
};
const closePetForm = () => {
    isPetFormOpen.value = false;
    editPet.value = null;
};

// Deletion Logic
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const itemToDelete = ref<{type: string, id: number} | null>(null);

const confirmDelete = (type: string, id: number) => {
    itemToDelete.value = { type, id };
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    isDeleting.value = true;
    
    let routeName = '';
    let params: any = { community_slug: communitySlug.value };
    
    if (itemToDelete.value.type === 'family') {
        routeName = 'tenant.resident.census.family.destroy';
        params.familyMember = itemToDelete.value.id;
    } else if (itemToDelete.value.type === 'vehicle') {
        routeName = 'tenant.resident.census.vehicles.destroy';
        params.vehicle = itemToDelete.value.id;
    } else if (itemToDelete.value.type === 'pet') {
        routeName = 'tenant.resident.census.pets.destroy';
        params.pet = itemToDelete.value.id;
    }

    router.delete(route(routeName, params), {
        onSuccess: () => {
            showToast('Elemento eliminado exitosamente', 'success');
            isDeleteModalOpen.value = false;
            itemToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const getRelationshipLabel = (value: string) => {
    const map: Record<string, string> = {
        spouse: 'Cónyuge',
        child: 'Hijo(a)',
        parent: 'Padre/Madre',
        other: 'Otro'
    };
    return map[value] || value;
};

const getVehicleTypeLabel = (value: string) => {
    const map: Record<string, string> = {
        car: 'Carro',
        motorcycle: 'Moto',
        bicycle: 'Bicicleta',
        other: 'Otro'
    };
    return map[value] || value;
};

const getSpeciesLabel = (value: string) => {
    const map: Record<string, string> = {
        dog: 'Perro',
        cat: 'Gato',
        other: 'Otro'
    };
    return map[value] || value;
};
</script>

<template>
    <Head title="Censo Residente" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Censo Residente
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Bento Grid / Tabs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <button @click="activeTab = 'family'" :class="[activeTab === 'family' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-white hover:bg-gray-50', 'relative flex items-center p-6 space-x-4 border text-card-foreground rounded-lg shadow-sm border-gray-100 transition']">
                        <div :class="[activeTab === 'family' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500', 'p-3 rounded-full']">
                            <UserGroupIcon class="w-6 h-6" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-gray-900">Grupo Familiar</h3>
                            <p class="text-sm text-gray-500">{{ familyMembers.length }} registrados</p>
                        </div>
                    </button>

                    <button @click="activeTab = 'vehicles'" :class="[activeTab === 'vehicles' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-white hover:bg-gray-50', 'relative flex items-center p-6 space-x-4 border text-card-foreground rounded-lg shadow-sm border-gray-100 transition']">
                        <div :class="[activeTab === 'vehicles' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500', 'p-3 rounded-full']">
                            <TruckIcon class="w-6 h-6" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-gray-900">Vehículos</h3>
                            <p class="text-sm text-gray-500">{{ vehicles.length }} registrados</p>
                        </div>
                    </button>

                    <button @click="activeTab = 'pets'" :class="[activeTab === 'pets' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-white hover:bg-gray-50', 'relative flex items-center p-6 space-x-4 border text-card-foreground rounded-lg shadow-sm border-gray-100 transition']">
                        <div :class="[activeTab === 'pets' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500', 'p-3 rounded-full']">
                            <HeartIcon class="w-6 h-6" />
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-gray-900">Mascotas</h3>
                            <p class="text-sm text-gray-500">{{ pets.length }} registradas</p>
                        </div>
                    </button>
                </div>

                <!-- Family Tab -->
                <div v-show="activeTab === 'family'" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <h3 class="font-semibold leading-none tracking-tight">Grupo Familiar</h3>
                            <p class="text-sm text-gray-500 mt-1">Personas que residen contigo en la unidad.</p>
                        </div>
                        <button @click="openFamilyForm()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Agregar Familiar
                        </button>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parentesco</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="member in familyMembers" :key="member.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ member.name }}</div>
                                            <span v-if="member.is_minor" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menor de edad</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ getRelationshipLabel(member.relationship) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ member.contact_phone || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openFamilyForm(member)" class="text-gray-400 hover:text-indigo-600 transition"><PencilIcon class="w-5 h-5" /></button>
                                            <button @click="confirmDelete('family', member.id)" class="text-gray-400 hover:text-red-600 transition"><TrashIcon class="w-5 h-5" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="familyMembers.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No tienes familiares registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vehicles Tab -->
                <div v-show="activeTab === 'vehicles'" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <h3 class="font-semibold leading-none tracking-tight">Vehículos</h3>
                            <p class="text-sm text-gray-500 mt-1">Carros, motos o bicicletas registrados.</p>
                        </div>
                        <button @click="openVehicleForm()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Agregar Vehículo
                        </button>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca/Color</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="vehicle in vehicles" :key="vehicle.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">{{ vehicle.license_plate }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ getVehicleTypeLabel(vehicle.type) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ vehicle.brand || 'N/A' }} / {{ vehicle.color || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openVehicleForm(vehicle)" class="text-gray-400 hover:text-indigo-600 transition"><PencilIcon class="w-5 h-5" /></button>
                                            <button @click="confirmDelete('vehicle', vehicle.id)" class="text-gray-400 hover:text-red-600 transition"><TrashIcon class="w-5 h-5" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="vehicles.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No tienes vehículos registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pets Tab -->
                <div v-show="activeTab === 'pets'" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <h3 class="font-semibold leading-none tracking-tight">Mascotas</h3>
                            <p class="text-sm text-gray-500 mt-1">Mascotas que residen contigo en la unidad.</p>
                        </div>
                        <button @click="openPetForm()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Agregar Mascota
                        </button>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especie</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raza</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="pet in pets" :key="pet.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pet.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ getSpeciesLabel(pet.species) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pet.breed || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openPetForm(pet)" class="text-gray-400 hover:text-indigo-600 transition"><PencilIcon class="w-5 h-5" /></button>
                                            <button @click="confirmDelete('pet', pet.id)" class="text-gray-400 hover:text-red-600 transition"><TrashIcon class="w-5 h-5" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="pets.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No tienes mascotas registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <FamilyMemberFormModal :show="isFamilyFormOpen" :family-member="editFamilyMember" @close="closeFamilyForm" />
        <VehicleFormModal :show="isVehicleFormOpen" :vehicle="editVehicle" @close="closeVehicleForm" />
        <PetFormModal :show="isPetFormOpen" :pet="editPet" @close="closePetForm" />

        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            :processing="isDeleting"
            @close="isDeleteModalOpen = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
