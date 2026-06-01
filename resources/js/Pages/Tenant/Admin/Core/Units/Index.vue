<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import UnitSlideOver from './Components/UnitSlideOver.vue';
import UnitFormModal from './Components/UnitFormModal.vue';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { PencilIcon, TrashIcon, UserGroupIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    units: {
        data: Array<{
            id: number;
            identifier: string;
            type: string;
            status: string;
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

const isSlideOverOpen = ref(false);
const selectedUnitId = ref<number | null>(null);
const isLoadingUnit = ref(false);

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

const openSlideOver = (unitId: number) => {
    selectedUnitId.value = unitId;
    isSlideOverOpen.value = true;
    isLoadingUnit.value = true;
    
    // The SlideOver component handles the data fetching based on these reactive props
    // We orchestrate the opening here
    setTimeout(() => {
        isLoadingUnit.value = false;
    }, 100);
};

const closeSlideOver = () => {
    isSlideOverOpen.value = false;
    selectedUnitId.value = null;
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
        <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Unidades</h2>
            <button
                @click="openFormModal(null)"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Nueva Unidad
            </button>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                
                <!-- Search Bar -->
                <div class="flex items-center justify-between">
                    <div class="relative w-full max-w-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                        <input type="text" v-model="search" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Buscar unidad..." />
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Identificador</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="unit in units.data" :key="unit.id" @click="openSlideOver(unit.id)" class="cursor-pointer hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ unit.identifier }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ getTaxonomyLabel('property_type', unit.type) }}
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
                                            <button @click.stop="openSlideOver(unit.id)" class="text-gray-400 hover:text-primary transition" title="Residentes" aria-label="Residentes">
                                                <UserGroupIcon class="w-5 h-5" />
                                            </button>
                                            <button @click.stop="openFormModal(unit.id)" class="text-gray-400 hover:text-primary transition" title="Editar" aria-label="Editar">
                                                <PencilIcon class="w-5 h-5" />
                                            </button>
                                            <button @click.stop="deleteUnit(unit.id)" class="text-gray-400 hover:text-red-600 transition" title="Borrar" aria-label="Borrar">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="units.data.length === 0">
                                    <td colspan="4" class="py-8 text-center text-sm text-gray-500">
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

        <UnitSlideOver 
            :is-open="isSlideOverOpen"
            :unit-id="selectedUnitId"
            :is-loading="isLoadingUnit"
            @close="closeSlideOver"
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
