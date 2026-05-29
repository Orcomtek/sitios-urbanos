<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';
import UnitSlideOver from './Components/UnitSlideOver.vue';

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
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const isSlideOverOpen = ref(false);
const selectedUnitId = ref<number | null>(null);
const isLoadingUnit = ref(false);

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

const deleteUnit = (unitId: number) => {
    if (window.confirm('¿Estás seguro de eliminar esta unidad?')) {
        router.delete(route('tenant.admin.core.units.destroy', { community_slug: communitySlug.value, unit: unitId }));
    }
};
</script>

<template>
    <Head title="Unidades" />

    <AppLayout>
        <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Unidades</h2>
            <Link
                :href="route('tenant.admin.core.units.create', { community_slug: communitySlug })"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Nueva Unidad
            </Link>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
                                        <button @click.stop="openSlideOver(unit.id)" class="text-indigo-600 hover:text-indigo-900">Residentes</button>
                                        <Link :href="route('tenant.admin.core.units.edit', { community_slug: communitySlug, unit: unit.id })" class="text-indigo-600 hover:text-indigo-900 ml-4">Editar</Link>
                                        <button @click.stop="deleteUnit(unit.id)" class="text-red-600 hover:text-red-900 ml-4">Borrar</button>
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
                </div>
            </div>
        </div>

        <UnitSlideOver 
            :is-open="isSlideOverOpen" 
            :unit-id="selectedUnitId"
            :is-loading="isLoadingUnit"
            @close="closeSlideOver" 
        />
    </AppLayout>
</template>
