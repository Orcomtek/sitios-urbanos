<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
    residentId: number | null;
    units: Array<{ id: number; identifier: string }>;
}>();

const emit = defineEmits(['close']);

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const isEditing = computed(() => !!props.residentId);
const isLoading = ref(false);

const form = useForm({
    unit_id: '',
    full_name: '',
    email: '',
    phone: '',
    resident_type: '',
    is_active: true,
    pays_administration: false,
});

watch(() => props.show, async (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
        
        if (props.residentId) {
            isLoading.value = true;
            try {
                const response = await axios.get(route('tenant.admin.core.residents.show', { community_slug: communitySlug.value, resident: props.residentId }));
                const resident = response.data.resident;
                form.unit_id = resident.unit_id || '';
                form.full_name = resident.full_name || '';
                form.email = resident.email || '';
                form.phone = resident.phone || '';
                form.resident_type = resident.resident_type || '';
                form.is_active = resident.is_active ?? true;
                form.pays_administration = resident.pays_administration ?? false;
            } catch (e) {
                showToast('Error al cargar el residente', 'error');
                emit('close');
            } finally {
                isLoading.value = false;
            }
        } else {
            form.resident_type = 'tenant';
            form.is_active = true;
            form.pays_administration = false;
        }
    }
});

const submit = () => {
    if (isEditing.value && props.residentId) {
        form.put(route('tenant.admin.core.residents.update', { community_slug: communitySlug.value, resident: props.residentId }), {
            onSuccess: () => {
                showToast('Residente actualizado exitosamente', 'success');
                emit('close');
            }
        });
    } else {
        form.post(route('tenant.admin.core.residents.store', { community_slug: communitySlug.value }), {
            onSuccess: () => {
                showToast('Residente creado exitosamente', 'success');
                emit('close');
            }
        });
    }
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <div class="px-6 py-4">
            <div class="text-lg font-medium text-gray-900">
                {{ isEditing ? 'Editar Residente' : 'Nuevo Residente' }}
            </div>

            <div v-if="isLoading" class="mt-4 flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <form v-else @submit.prevent="submit" class="mt-4">
                <div class="space-y-6">
                    
                    <div>
                        <label for="unit_id" class="block text-sm font-medium leading-6 text-gray-900">Unidad</label>
                        <div class="mt-2">
                            <select id="unit_id" v-model="form.unit_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="" disabled>Seleccione una unidad</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.identifier }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors.unit_id" class="mt-2 text-sm text-red-600">{{ form.errors.unit_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="full_name" class="block text-sm font-medium leading-6 text-gray-900">Nombre Completo</label>
                            <div class="mt-2">
                                <input type="text" id="full_name" v-model="form.full_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <p v-if="form.errors.full_name" class="mt-2 text-sm text-red-600">{{ form.errors.full_name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Correo Electrónico (Opcional)</label>
                            <div class="mt-2">
                                <input type="email" id="email" v-model="form.email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Teléfono (Opcional)</label>
                            <div class="mt-2">
                                <input type="text" id="phone" v-model="form.phone" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <label for="resident_type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Residente</label>
                            <div class="mt-2">
                                <select id="resident_type" v-model="form.resident_type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="" disabled>Seleccione un tipo</option>
                                    <option v-for="type in (page.props.taxonomies as any)?.resident_type" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <p v-if="form.errors.resident_type" class="mt-2 text-sm text-red-600">{{ form.errors.resident_type }}</p>
                        </div>

                        <div class="flex flex-col gap-y-4">
                            <div class="relative flex gap-x-3 mt-8">
                                <div class="flex h-6 items-center">
                                    <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="is_active" class="font-medium text-gray-900">Activo</label>
                                    <p class="text-gray-500">¿El residente vive actualmente en la unidad?</p>
                                </div>
                            </div>
                            <p v-if="form.errors.is_active" class="mt-2 text-sm text-red-600">{{ form.errors.is_active }}</p>

                            <div class="relative flex gap-x-3">
                                <div class="flex h-6 items-center">
                                    <input id="pays_administration" v-model="form.pays_administration" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="pays_administration" class="font-medium text-gray-900">Paga Administración</label>
                                    <p class="text-gray-500">¿Se encarga del pago de administración?</p>
                                </div>
                            </div>
                            <p v-if="form.errors.pays_administration" class="mt-2 text-sm text-red-600">{{ form.errors.pays_administration }}</p>
                        </div>
                    </div>

                    <!-- Alert Warning -->
                    <div v-if="form.resident_type === 'tenant' && form.is_active" class="rounded-md bg-yellow-50 p-4 border border-yellow-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Atención: Regla de Inquilino Único</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Si ya existe un inquilino activo en esta unidad, será desactivado automáticamente junto con sus dependientes al guardar.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex items-center justify-end gap-x-3">
                    <button type="button" @click="close" class="text-sm font-semibold leading-6 text-gray-900 px-3 py-2 hover:bg-gray-50 rounded-md">Cancelar</button>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Residente' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
