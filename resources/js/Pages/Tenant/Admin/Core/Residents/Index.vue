<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { PencilIcon, TrashIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import Pagination from '@/Components/ui/Pagination.vue';
import ResidentFormModal from './Components/ResidentFormModal.vue';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    residents: {
        data: Array<{
            id: number;
            full_name: string;
            email: string | null;
            phone: string | null;
            resident_type: string;
            is_active: boolean;
            unit: {
                id: number;
                identifier: string;
            };
        }>;
        links: any[];
    };
    filters?: {
        search?: string;
        status?: string;
    };
    units: Array<{
        id: number;
        identifier: string;
    }>;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'active');

let searchTimeout: ReturnType<typeof setTimeout>;
watch([search, selectedStatus], ([newSearch, newStatus]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('tenant.admin.core.residents.index', { community_slug: communitySlug.value }),
            { search: newSearch, status: newStatus },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

const isFormModalOpen = ref(false);
const editResidentId = ref<number | null>(null);

const openFormModal = (residentId: number | null = null) => {
    editResidentId.value = residentId;
    isFormModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    editResidentId.value = null;
};

const isDispatching = ref(false);
const dispatchInvitations = () => {
    isDispatching.value = true;
    router.post(route('tenant.admin.core.residents.dispatch-invitations', { community_slug: communitySlug.value }), {}, {
        onSuccess: () => {
            showToast('Invitaciones encoladas exitosamente', 'success');
        },
        onFinish: () => {
            isDispatching.value = false;
        }
    });
};

const isDeleteModalOpen = ref(false);
const deleteResidentId = ref<number | null>(null);
const isDeleting = ref(false);

const deleteResident = (residentId: number) => {
    deleteResidentId.value = residentId;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deleteResidentId.value) return;
    
    isDeleting.value = true;
    router.delete(route('tenant.admin.core.residents.destroy', { community_slug: communitySlug.value, resident: deleteResidentId.value }), {
        onSuccess: () => {
            showToast('Residente eliminado exitosamente', 'success');
            isDeleteModalOpen.value = false;
            deleteResidentId.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const getTaxonomyLabel = (type: string, value: string) => {
    const taxonomies = (page.props.taxonomies as any)?.[type] || [];
    const item = taxonomies.find((t: any) => t.value === value);
    return item ? item.label : value;
};
</script>

<template>
    <Head title="Residentes" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Residentes</h2>
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
                                <input type="text" v-model="search" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Buscar residente..." />
                            </div>
                            <div class="relative">
                                <select v-model="selectedStatus" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                    <option value="all">Todos</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                @click="dispatchInvitations"
                                :disabled="isDispatching"
                                class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-sm font-medium transition-colors disabled:opacity-50 whitespace-nowrap"
                            >
                                {{ isDispatching ? 'Enviando...' : 'Enviar Invitaciones' }}
                            </button>
                            <button
                                @click="openFormModal(null)"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap"
                            >
                                + Añadir Residente
                            </button>
                        </div>
                    </div>
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Nombre</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Unidad</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="resident in residents.data" :key="resident.id">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                        {{ resident.full_name }}
                                        <div v-if="resident.email" class="text-xs font-normal text-gray-500">{{ resident.email }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ resident.unit?.identifier }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                              :class="{
                                                  'bg-blue-50 text-blue-700 ring-blue-600/20': resident.resident_type === 'owner',
                                                  'bg-purple-50 text-purple-700 ring-purple-600/20': resident.resident_type === 'tenant',
                                                  'bg-gray-50 text-gray-700 ring-gray-600/20': resident.resident_type !== 'owner' && resident.resident_type !== 'tenant'
                                              }">
                                            {{ getTaxonomyLabel('resident_type', resident.resident_type) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span v-if="resident.is_active" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activo</span>
                                        <span v-else class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactivo</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click.stop="openFormModal(resident.id)" class="relative group text-gray-400 hover:text-indigo-600 transition" aria-label="Editar">
                                                <PencilIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Editar</span>
                                            </button>
                                            <button @click.stop="deleteResident(resident.id)" class="relative group text-gray-400 hover:text-red-600 transition" aria-label="Eliminar">
                                                <TrashIcon class="w-5 h-5" />
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 z-10 left-1/2 -translate-x-1/2">Eliminar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="residents.data.length === 0">
                                    <td colspan="5" class="py-8 text-center text-sm text-gray-500">
                                        No hay residentes registrados en esta comunidad.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <Pagination :links="residents.links" />
                    </div>
                </div>
            </div>
        </div>

        <ResidentFormModal
            :show="isFormModalOpen"
            :resident-id="editResidentId"
            :units="units"
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
