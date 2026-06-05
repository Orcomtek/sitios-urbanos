<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import UnitResidentsModal from './Components/UnitResidentsModal.vue';
import UnitFormModal from './Components/UnitFormModal.vue';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { PencilIcon, TrashIcon, UserGroupIcon, MagnifyingGlassIcon, PhoneIcon, ChatBubbleOvalLeftIcon, EnvelopeIcon, ArchiveBoxIcon, KeyIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    units: {
        data: Array<{
            id: number;
            identifier: string;
            type: string;
            type_label?: string;
            status: string;
            is_rented?: boolean;
            residents?: any[];
            parking?: string | null;
            storage?: string | null;
            owner?: {
                name: string;
                phone: string;
                email: string;
            } | null;
        }>;
        links: any[];
    };
    filters?: {
        search?: string;
    };
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const search = ref(props.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('tenant.admin.core.units.index', { community_slug: communitySlug.value }),
            { search: value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

const isResidentsModalOpen = ref(false);
const selectedUnit = ref<any>(null);

const isFormModalOpen = ref(false);
const editUnitId = ref<number | null>(null);

const openFormModal = (unitId: number | null = null) => {
    editUnitId.value = unitId;
    isFormModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    editUnitId.value = null;
};

const getTaxonomyLabel = (type: string, value: string) => {
    const taxonomies = (page.props.taxonomies as any)?.[type] || [];
    const item = taxonomies.find((t: any) => t.value === value);
    return item ? item.label : value;
};

const openResidentsModal = (unit: any) => {
    selectedUnit.value = unit;
    isResidentsModalOpen.value = true;
};

const closeResidentsModal = () => {
    isResidentsModalOpen.value = false;
    selectedUnit.value = null;
};

const isDeleteModalOpen = ref(false);
const deleteUnitId = ref<number | null>(null);
const isDeleting = ref(false);

const deleteUnit = (unitId: number) => {
    deleteUnitId.value = unitId;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deleteUnitId.value) return;
    
    isDeleting.value = true;
    router.delete(route('tenant.admin.core.units.destroy', { community_slug: communitySlug.value, unit: deleteUnitId.value }), {
        onSuccess: () => {
            showToast('Unidad eliminada exitosamente', 'success');
            isDeleteModalOpen.value = false;
            deleteUnitId.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Unidades" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Unidades</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Search, Filters and Actions -->
                    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 w-full sm:w-auto flex-wrap">
                            <div class="relative w-full max-w-sm sm:w-80">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input type="text" v-model="search" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Buscar unidad..." />
                            </div>
                        </div>
                        <div>
                            <button
                                @click="openFormModal(null)"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap"
                            >
                                + Añadir Unidad
                            </button>
                        </div>
                    </div>

                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Identificador</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Propietario / Contacto</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="unit in units.data" :key="unit.id" @click="openResidentsModal(unit)" class="cursor-pointer hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                        {{ unit.identifier }}
                                        <span v-if="unit.is_rented" class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Arrendada</span>
                                        <div class="flex flex-row gap-2 mt-1">
                                            <span v-if="unit.parking" class="relative group inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-default">
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">{{ unit.parking }}</span>
                                                <KeyIcon class="w-3 h-3 text-gray-400" /> Parqueadero
                                            </span>
                                            <span v-if="unit.storage" class="relative group inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-default">
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">{{ unit.storage }}</span>
                                                <ArchiveBoxIcon class="w-3 h-3 text-gray-400" /> Depósito
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ unit.type_label || getTaxonomyLabel('property_type', unit.type) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <div v-if="unit.owner" class="flex flex-col">
                                            <span class="font-medium text-gray-900">{{ unit.owner.name }}</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <a v-if="unit.owner.phone" :href="'tel:' + unit.owner.phone" class="relative group text-gray-400 hover:text-green-600 transition" @click.stop>
                                                    <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Llamar al {{ unit.owner.phone }}</span>
                                                    <PhoneIcon class="w-4 h-4" />
                                                </a>
                                                <a v-if="unit.owner.phone" :href="'https://wa.me/' + unit.owner.phone.replace(/\D/g,'') + '?text=' + encodeURIComponent('Hola ' + unit.owner.name + ', te escribimos desde los canales de comunicación del conjunto ' + (($page.props.tenant as any)?.community?.name || '') + ' para brindarte la siguiente información:')" target="_blank" class="relative group text-gray-400 hover:text-green-500 transition" @click.stop>
                                                    <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Enviar WhatsApp a {{ unit.owner.name }}</span>
                                                    <ChatBubbleOvalLeftIcon class="w-4 h-4" />
                                                </a>
                                                <a v-if="unit.owner.email" :href="'mailto:' + unit.owner.email" class="relative group text-gray-400 hover:text-blue-600 transition" @click.stop>
                                                    <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Enviar mensaje a {{ unit.owner.email }}</span>
                                                    <EnvelopeIcon class="w-4 h-4" />
                                                </a>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <span class="text-gray-400 italic text-xs">Sin propietario asignado</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                              :class="{
                                                  'bg-green-50 text-green-700 ring-green-600/20': unit.status === 'occupied',
                                                  'bg-yellow-50 text-yellow-800 ring-yellow-600/20': unit.status === 'vacant',
                                                  'bg-gray-50 text-gray-600 ring-gray-500/10': unit.status !== 'occupied' && unit.status !== 'vacant'
                                              }">
                                            {{ getTaxonomyLabel('unit_status', unit.status) }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click.stop="openResidentsModal(unit)" class="relative group text-gray-400 hover:text-indigo-600 transition" aria-label="Ver Residentes">
                                                <UserGroupIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Ver Residentes</span>
                                            </button>
                                            <button @click.stop="openFormModal(unit.id)" class="relative group text-gray-400 hover:text-indigo-600 transition" aria-label="Editar">
                                                <PencilIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Editar</span>
                                            </button>
                                            <button @click.stop="deleteUnit(unit.id)" class="relative group text-gray-400 hover:text-red-600 transition" aria-label="Eliminar">
                                                <TrashIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Eliminar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="units.data.length === 0">
                                    <td colspan="5" class="py-8 text-center text-sm text-gray-500">
                                        No hay unidades registradas en esta comunidad.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <Pagination :links="units.links" />
                    </div>
                </div>
            </div>
        </div>

        <UnitResidentsModal 
            :show="isResidentsModalOpen"
            :unit="selectedUnit"
            @close="closeResidentsModal"
        />

        <UnitFormModal
            :show="isFormModalOpen"
            :unit-id="editUnitId"
            @close="closeFormModal"
        />

        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            :processing="isDeleting"
            @close="isDeleteModalOpen = false"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
