<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
    unitId: number | null;
}>();

const emit = defineEmits(['close']);

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const isEditing = computed(() => !!props.unitId);
const isLoading = ref(false);

const form = useForm({
    identifier: '',
    property_type: '',
    status: '',
    amenities: [] as string[],
});

watch(() => props.show, async (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
        
        if (props.unitId) {
            isLoading.value = true;
            try {
                const response = await axios.get(route('tenant.admin.core.units.show', { community_slug: communitySlug.value, unit: props.unitId }));
                const unit = response.data.unit;
                form.identifier = unit.identifier || '';
                form.property_type = unit.property_type || '';
                form.status = unit.status || '';
                form.amenities = unit.amenities || [];
            } catch (e) {
                showToast('Error al cargar la unidad', 'error');
                emit('close');
            } finally {
                isLoading.value = false;
            }
        }
    }
});

const submit = () => {
    if (isEditing.value && props.unitId) {
        form.put(route('tenant.admin.core.units.update', { community_slug: communitySlug.value, unit: props.unitId }), {
            onSuccess: () => {
                showToast('Unidad actualizada exitosamente', 'success');
                emit('close');
            }
        });
    } else {
        form.post(route('tenant.admin.core.units.store', { community_slug: communitySlug.value }), {
            onSuccess: () => {
                showToast('Unidad creada exitosamente', 'success');
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
                {{ isEditing ? 'Editar Unidad' : 'Nueva Unidad' }}
            </div>

            <div v-if="isLoading" class="mt-4 flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
            </div>

            <form v-else @submit.prevent="submit" class="mt-4">
                <div class="space-y-6">
                    <div>
                        <label for="identifier" class="block text-sm font-medium leading-6 text-gray-900">Identificador</label>
                        <div class="mt-2">
                            <input type="text" id="identifier" v-model="form.identifier" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6" placeholder="Ej: Apto 101" />
                        </div>
                        <p v-if="form.errors.identifier" class="mt-2 text-sm text-red-600">{{ form.errors.identifier }}</p>
                    </div>

                    <div>
                        <label for="property_type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Unidad</label>
                        <div class="mt-2">
                            <select id="property_type" v-model="form.property_type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6">
                                <option value="" disabled>Seleccione un tipo</option>
                                <option v-for="type in (page.props.taxonomies as any)?.property_type" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>
                        <p v-if="form.errors.property_type" class="mt-2 text-sm text-red-600">{{ form.errors.property_type }}</p>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Estado</label>
                        <div class="mt-2">
                            <select id="status" v-model="form.status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6">
                                <option value="" disabled>Seleccione un estado</option>
                                <option v-for="status in (page.props.taxonomies as any)?.unit_status" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                        <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Amenidades (Características)</h3>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="amenity in (page.props.taxonomies as any)?.unit_amenity" :key="amenity.value" class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input 
                                        :id="`amenity_${amenity.value}`" 
                                        :value="amenity.value" 
                                        v-model="form.amenities" 
                                        type="checkbox" 
                                        class="h-4 w-4 rounded border-gray-300 text-slate-900 focus:ring-slate-900" 
                                    />
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label :for="`amenity_${amenity.value}`" class="font-medium text-gray-900">{{ amenity.label }}</label>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.amenities" class="mt-2 text-sm text-red-600">{{ form.errors.amenities }}</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-3">
                    <button type="button" @click="close" class="text-sm font-semibold leading-6 text-gray-900 px-3 py-2 hover:bg-gray-50 rounded-md">Cancelar</button>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus:ring-emerald-500 focus-visible:outline-emerald-500 transition-colors">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Unidad' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
