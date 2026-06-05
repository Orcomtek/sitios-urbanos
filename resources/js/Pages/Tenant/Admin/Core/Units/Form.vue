<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    unit: {
        id?: number;
        identifier: string;
        property_type: string;
        status: string;
        amenities?: string[];
    };
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const isEditing = computed(() => !!props.unit.id);

const form = useForm({
    identifier: props.unit.identifier || '',
    property_type: props.unit.property_type || '',
    status: props.unit.status || '',
    amenities: props.unit.amenities || [],
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('tenant.admin.core.units.update', { community_slug: communitySlug.value, unit: props.unit.id }));
    } else {
        form.post(route('tenant.admin.core.units.store', { community_slug: communitySlug.value }));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Unidad' : 'Nueva Unidad'" />

    <AppLayout>
        <template #header>
            <div class="px-4 py-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isEditing ? 'Editar Unidad' : 'Nueva Unidad' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <div>
                                <label for="identifier" class="block text-sm font-medium leading-6 text-gray-900">Identificador</label>
                                <div class="mt-2">
                                    <input type="text" id="identifier" v-model="form.identifier" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Ej: Apto 101" />
                                </div>
                                <p v-if="form.errors.identifier" class="mt-2 text-sm text-red-600">{{ form.errors.identifier }}</p>
                            </div>

                            <div>
                                <label for="property_type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Unidad</label>
                                <div class="mt-2">
                                    <select id="property_type" v-model="form.property_type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
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
                                    <select id="status" v-model="form.status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
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
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" 
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

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <button type="button" onclick="window.history.back()" class="text-sm font-semibold leading-6 text-gray-900">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus:ring-emerald-500 focus-visible:outline-emerald-500 transition-colors">
                                {{ isEditing ? 'Guardar Cambios' : 'Crear Unidad' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
